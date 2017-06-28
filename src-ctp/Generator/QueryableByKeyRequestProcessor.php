<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Generator;

use Ctp\Client\SphereRequest;
use Ctp\Model\Collection;
use Ctp\Model\PagedQueryResult;
use Ctp\Model\Reference;
use Ctp\Model\ResultMapper;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

class QueryableByKeyRequestProcessor extends AbstractProcessor
{
    const REQUEST_SUFFIX = 'ByKeyGetRequest';
    private $path;
    private $outputPath;
    private $baseNamespace;

    /**
     * ResourceProcessor constructor.
     * @param $path
     * @param $outputPath
     */
    public function __construct($baseNamespace, $path, $outputPath)
    {
        $this->baseNamespace = $baseNamespace;
        $this->path = $path;
        $this->outputPath = $outputPath;
    }

    public function getAnnotation()
    {
        return Queryable::class;
    }

    /**
     * @inheritDoc
     */
    public function process(ReflectionClass $class, $annotation)
    {
        if (!$annotation instanceof Queryable) {
            return [];
        }
        if (!in_array(QueryType::BY_KEY, $annotation->get)) {
            return [];
        }
        $relativePath = trim(str_replace($this->path, '', dirname($class->getFileName())), '/');

        $factory = new BuilderFactory();
        $builder = $factory->namespace($this->baseNamespace . '\\' . $relativePath);

        $modelPath = str_replace($this->path, $this->outputPath, dirname($class->getFileName()));

        $request = new ReflectionClass(SphereRequest::class);
        $queryResult = new ReflectionClass(PagedQueryResult::class);
        $className = $class->getShortName() . static::REQUEST_SUFFIX;
        $resultType = $class->getShortName();

        $classBuilder = $factory->class($className)->extend($request->getShortName())->setDocComment(
            '/**
              * @method ' . $resultType . ' map(ResponseInterface $response)
              */'
        );
        $classBuilder->addStmt(new Stmt\ClassConst([
            new Node\Const_('RESULT_TYPE', new Expr\ClassConstFetch(
                new Node\Name($resultType),
                'class'
            ))
        ]));

        $body = '
        $uri = sprintf(\'' . $annotation->uri . '/key=%s\', $key);
        parent::__construct(\'' . $annotation->method . '\', $uri, $headers);
        ';
        $classBuilder->addStmt(
            $factory->method('__construct')
                ->makePublic()
                ->addParam(
                    $factory->param('key')
                )
                ->addParam(
                    $factory->param('headers')
                        ->setDefault(new Expr\Array_([], ['kind' => Expr\Array_::KIND_SHORT]))->setTypeHint('array')
                )
                ->addStmts(
                    (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body)
                )
        );

        $body = 'return $this->getUri()->withPath(sprintf(\'' . $annotation->uri . '/key=%s\', $key));';
        $classBuilder->addStmt(
            $factory->method('withKey')
                ->makePublic()
                ->addParam($factory->param('key'))
                ->addStmts(
                    (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body)
                )
        );

        $builder->addStmt($factory->use($class->getNamespaceName() . '\\' . $resultType));
        $builder->addStmt($factory->use(ResponseInterface::class));
        $builder->addStmt($factory->use(SphereRequest::class));
        $builder->addStmt($classBuilder);

        $node = $builder->getNode();
        $stmts = [$node];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor(
            new NamespaceChangeVisitor($class->getNamespaceName(), $class->getNamespaceName())
        ); // we will shorten the resolved names
        $traverser->traverse($stmts);

        $fileName = $modelPath . '/' . $className . '.php';
        return [$this->writeClass($fileName, $stmts)];
    }

    private function camel2dashed($string)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
    }
}
