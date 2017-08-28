<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

interface LocalizedStringQuerySortingModel
{
    /**
     * @param $locale
     * @return StringQueryModel
     */
    public function lang($locale);

    /**
     * @param $locale
     * @return StringQueryModel
     */
    public function locale($locale);

    /**
     * @return QueryPredicate
     */
    public function isNotPresent();

    /**
     * @return QueryPredicate
     */
    public function isPresent();
}
