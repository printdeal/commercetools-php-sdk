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
    private $path;

    /**
     * @var Processor[]
     */
    private $processors;

    private $annotations;

    /**
     * AnnotationRunner constructor.
     * @param $path
     * @param array $annotationClasses
     * @param Processor[] $processors
     */
    public function __construct($path, array $processors = [], array $annotationClasses = [])
    {
        $this->path = $path;
        $annotationClasses = [
            JsonResource::class,
            JsonField::class,
            Discriminator::class,
            DiscriminatorValue::class,
            CollectionType::class,
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
        $path = realpath($this->path);

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
                $annotationVisitor = new AnnotationVisitor($processor->getAnnotation());
                $traverser = new NodeTraverser();
                $traverser->addVisitor(new NameResolver()); // we will need resolved names
                $traverser->addVisitor($annotationVisitor);
                $code = file_get_contents($file);
                $stmts = $parser->parse($code);

                $traverser->traverse($stmts);
                $annotation = $processor->getAnnotation();
                if ($annotationVisitor->getAnnotatedClass() instanceof $annotation) {
                    $processor->process($annotationVisitor->getReflectedClass(), $annotationVisitor->getAnnotatedClass());
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
