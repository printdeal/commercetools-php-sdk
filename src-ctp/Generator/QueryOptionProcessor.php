<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Generator;

use PhpParser\BuilderFactory;
use PhpParser\ParserFactory;

class QueryOptionProcessor
{
    public function process($className, Restable $annotation)
    {
        $factory = new BuilderFactory();

        $stmts = [];
        foreach ($annotation->options as $option) {
            $body = 'return $this->withQueryParam(\'' . $option . '\', $' . lcfirst($option) . ');';
            $stmts[] = $factory->method('with' . ucfirst($option))
                ->makePublic()
                ->setDocComment(
                    '/**
                      * @param $' . lcfirst($option) . '
                      * @return ' . $className . '
                      */'
                )
                ->addParam($factory->param(lcfirst($option)))
                ->addStmts(
                    (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body)
                );
        }
        return $stmts;
    }


}
