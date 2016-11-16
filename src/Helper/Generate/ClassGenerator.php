<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use ReflectionClass;

class ClassGenerator
{
    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;
    protected $newDocBlock;
    protected $fields;
    protected $fieldNames;
    protected $includes;
    protected $uses;

    public function run($path, $outputPath, $oldNamespace, $newNamespace)
    {
        $path = realpath($path);
        $this->ensureDirExists($outputPath);
        $outputPath = realpath($outputPath);

        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');

        $this->generateFiles($phpFiles, $path, $outputPath, $oldNamespace, $newNamespace);
    }

    protected function generateFiles(\RegexIterator $phpFiles, $inputPath, $outputPath, $oldNamespace, $newNamespace)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
    
        $printer = new PrettyPrinter\Standard();

        $annotationClasses = [
            FieldType::class,
            DiscriminatorColumn::class,
            CollectionType::class,
        ];
        foreach ($phpFiles as $file) {
            $code = file_get_contents($file);
            $stmts = $parser->parse($code);

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver()); // we will need resolved names
            $traverser->addVisitor(new PropertyVisitor($annotationClasses));
            $traverser->addVisitor(new NamespaceChangeVisitor($oldNamespace, $newNamespace));
            $traverser->traverse($stmts);
//            echo '<pre>' . ($printer->prettyPrint($stmts));

            $outputFile = str_replace($inputPath, $outputPath, $file);
            $this->ensureDirExists(dirname($outputFile));

            file_put_contents($outputFile, '<?php ' . PHP_EOL . $printer->prettyPrint($stmts));
        }
    }

    protected function getJsonObjects(\RegexIterator $phpFiles)
    {
        $jsonObjects = [];
        foreach ($phpFiles as $phpFile) {
            $class = $this->getClassName($phpFile->getRealPath());

            if (!empty($class)) {
                if (in_array('Commercetools\Core\Model\Common\JsonObject', class_parents($class))) {
                    $jsonObjects[] = $class;
                }
            }
        }

        return $jsonObjects;
    }

    protected function getCollectionObjects(\RegexIterator $phpFiles)
    {
        $collectionObjects = [];
        foreach ($phpFiles as $phpFile) {
            $class = $this->getClassName($phpFile->getRealPath());

            if (!empty($class)) {
                if (in_array('Commercetools\Core\Model\Common\Collection', class_parents($class))) {
                    $collectionObjects[] = $class;
                }
            }
        }

        return $collectionObjects;
    }

    protected function getRequestObjects(\RegexIterator $phpFiles)
    {
        $requestObjects = [];
        foreach ($phpFiles as $phpFile) {
            $class = $this->getClassName($phpFile->getRealPath());

            if (!empty($class)) {
                if (in_array('Commercetools\Core\Request\AbstractApiRequest', class_parents($class))) {
                    $requestObjects[] = $class;
                }
            }
        }

        return $requestObjects;
    }

    protected function getClassName($fileName)
    {
        $tokens = $this->tokenize($fileName);
        $namespace = '';
        for ($index = 0; isset($tokens[$index]); $index++) {
            if (!isset($tokens[$index][0])) {
                continue;
            }
            if (T_NAMESPACE === $tokens[$index][0]) {
                $index += 2; // Skip namespace keyword and whitespace
                while (isset($tokens[$index]) && is_array($tokens[$index])) {
                    $namespace .= $tokens[$index++][1];
                }
            }
            if (T_CLASS === $tokens[$index][0]) {
                $index += 2; // Skip class keyword and whitespace
                $class = $namespace.'\\'.$tokens[$index][1];
                return $class;
            }
        }

        return null;
    }

    protected function tokenize($fileName)
    {
        $content = file_get_contents($fileName);
        return token_get_all($content);
    }

    protected function ensureDirExists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
