<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Generator;


interface Processor
{
    public function process();

    public function getAnnotations();

    public function setResult($annotation, $result);
}
