<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

abstract class AbstractProcessor implements Processor
{
    const MODEL_SUFFIX = 'Model';

    private $result;

    public function setResult($annotation, $result)
    {
        $this->result[$annotation] = $result;
    }

    public function getResult($annotation)
    {
        return $this->result[$annotation];
    }

    protected function writeClass($filename, $stmts)
    {
        $printer = new MyPrettyPrinter();
        $this->ensureDirExists(dirname($filename));
        file_put_contents($filename, '<?php ' . PHP_EOL . $printer->prettyPrint($stmts));
    }

    protected function ensureDirExists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
