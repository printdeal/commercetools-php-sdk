<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

interface QueryModel
{
    /**
     * @return string
     */
    public function getPathSegment();

    /**
     * @return QueryModel
     */
    public function getParent();
}
