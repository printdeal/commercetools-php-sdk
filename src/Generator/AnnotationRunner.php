<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Doctrine\Common\Annotations\AnnotationRegistry;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class AnnotationRunner
{
    /**
     * @var string
     */
    private $pathes;

    /**
     * @var Processor[]
     */
    private $processors;

    private $annotations;

    /**
     * AnnotationRunner constructor.
     * @param $pathes
     * @param array $annotationClasses
     * @param Processor[] $processors
     */
    public function __construct($pathes, array $processors = [], array $annotationClasses = [])
    {
        if (!is_array($pathes)) {
            $pathes = [$pathes];
        }
        $this->pathes = $pathes;

        $annotationClasses = [
            JsonResource::class,
            JsonField::class,
            Discriminator::class,
            DiscriminatorValue::class,
            Collectable::class,
            CollectionType::class,
            Referenceable::class
        ];
        $this->annotations = $annotationClasses;
        foreach ($this->annotations as $annotationClass) {
            $class = new \ReflectionClass($annotationClass);
            AnnotationRegistry::registerFile(
                $class->getFileName()
            );
        }
        $this->processors = $processors;
    }

    public function run()
    {
        foreach ($this->pathes as $path) {
            $this->runPath(realpath($path));
        }
    }
    public function runPath($path)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);

        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');
        $files = [];
        foreach ($phpFiles as $file) {
            $files[] = $file;
        }

        do {
            $file = array_shift($files);
            foreach ($this->processors as $processor) {
                $annotations = $processor->getAnnotation();
                if (!is_array($annotations)) {
                    $annotations = [$annotations];
                }
                foreach ($annotations as $annotation) {
                    $annotationVisitor = new AnnotationVisitor($annotation);
                    $traverser = new NodeTraverser();
                    $traverser->addVisitor(new NameResolver()); // we will need resolved names
                    $traverser->addVisitor($annotationVisitor);
                    $code = file_get_contents($file);
                    $stmts = $parser->parse($code);

                    $traverser->traverse($stmts);
                    if ($annotationVisitor->getAnnotatedClass() instanceof $annotation) {
                        $createdFiles = $processor->process(
                            $annotationVisitor->getReflectedClass(),
                            $annotationVisitor->getAnnotatedClass()
                        );
                        $files = array_merge($files, $createdFiles);
                    }
                }
            }
        } while (count($files) > 0);

//        foreach ($this->processors as $processor) {
//            $annotationVisitor = new AnnotationVisitor($processor->getAnnotation());
//            $traverser = new NodeTraverser();
//            $traverser->addVisitor(new NameResolver()); // we will need resolved names
//            $traverser->addVisitor($annotationVisitor);
//            foreach ($phpFiles as $file) {
//                $code = file_get_contents($file);
//                $stmts = $parser->parse($code);
//
//                $traverser->traverse($stmts);
//            }
//            foreach ($annotationVisitor->getAnnotatedClasses() as $className => $annotation) {
//                $processor->process($className, $annotation);
//            }
//        }
    }
}
