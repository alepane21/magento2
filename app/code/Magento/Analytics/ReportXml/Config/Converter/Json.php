<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\Config\Converter;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class Json
 *
 * Reader for config stored in json
 */
class Json implements ConverterInterface
{
    /**
     * Converts source to array
     *
     * @param mixed $source
     * @return array
     */
    public function convert($source)
    {
        return [];
    }
}