<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Serializer\LineItem;

use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollection;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainer;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainerInterface;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializer;
use OAT\Library\Lti1p3Ags\Serializer\JsonSerializerInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use RuntimeException;

class LineItemContainerSerializer implements LineItemContainerSerializerInterface
{
    /** @var LineItemFactoryInterface */
    private $lineItemFactory;

    /** @var JsonSerializerInterface */
    private $jsonSerializer;

    public function __construct(
        ?LineItemFactoryInterface $factory = null,
        ?JsonSerializerInterface $jsonSerializer = null
    ) {
        $this->lineItemFactory = $factory ?? new LineItemFactory();
        $this->jsonSerializer = $jsonSerializer ?? new JsonSerializer();
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function serialize(LineItemContainerInterface $container): string
    {
        try {
            return $this->jsonSerializer->serialize($container);
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during line item container serialization: %s', $exception->getMessage()),
                0,
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deserialize(string $data): LineItemContainerInterface
    {
        try {
            $deserializedData = $this->jsonSerializer->deserialize($data);
        } catch (RuntimeException $exception) {
            throw new LtiException(
                sprintf('Error during line item container deserialization: %s', $exception->getMessage()),
                0,
                $exception
            );
        }

        $collection = new LineItemCollection();

        foreach ($deserializedData['lineItems'] ?? [] as $lineItemData) {
            $collection->add($this->lineItemFactory->create($lineItemData));
        }

        return new LineItemContainer(
            $collection,
            $deserializedData['relationLink'] ?? null
        );
    }
}
