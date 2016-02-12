<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Model\Message;

use Commercetools\Core\Model\Common\DateTimeDecorator;
use Commercetools\Core\Model\Common\Reference;
use Commercetools\Core\Model\Category\Category;

/**
 * @package Commercetools\Core\Model\Message
 * @link https://dev.commercetools.com/http-api-projects-messages.html#category-created-message
 * @method string getId()
 * @method CategoryCreatedMessage setId(string $id = null)
 * @method DateTimeDecorator getCreatedAt()
 * @method CategoryCreatedMessage setCreatedAt(\DateTime $createdAt = null)
 * @method int getSequenceNumber()
 * @method CategoryCreatedMessage setSequenceNumber(int $sequenceNumber = null)
 * @method Reference getResource()
 * @method CategoryCreatedMessage setResource(Reference $resource = null)
 * @method int getResourceVersion()
 * @method CategoryCreatedMessage setResourceVersion(int $resourceVersion = null)
 * @method string getType()
 * @method CategoryCreatedMessage setType(string $type = null)
 * @method Category getCategory()
 * @method CategoryCreatedMessage setCategory(Category $category = null)
 */
class CategoryCreatedMessage extends Message
{
    const MESSAGE_TYPE = 'CategoryCreated';

    public function fieldDefinitions()
    {
        $definitions = parent::fieldDefinitions();
        $definitions['category'] = [static::TYPE => '\Commercetools\Core\Model\Category\Category'];

        return $definitions;
    }
}
