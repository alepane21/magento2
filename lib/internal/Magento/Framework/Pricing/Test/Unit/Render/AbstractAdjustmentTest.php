<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Render;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\Render\AbstractAdjustment;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Framework\Pricing\Render\AbstractAdjustment
 */
class AbstractAdjustmentTest extends TestCase
{
    /**
     * @var AbstractAdjustment|MockObject
     */
    protected $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    /**
     * @var array
     */
    protected $data;

    protected function setUp(): void
    {
        $this->priceCurrency = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->data = ['argument_one' => 1];

        $objectManager = new ObjectManager($this);
        $constructorArgs = $objectManager->getConstructArguments(
            AbstractAdjustment::class,
            [
                'priceCurrency' => $this->priceCurrency,
                'data' => $this->data
            ]
        );
        $this->model = $this->getMockBuilder(AbstractAdjustment::class)
            ->setConstructorArgs($constructorArgs)
            ->setMethods(['getData', 'setData', 'apply'])
            ->getMockForAbstractClass();
    }

    public function testConvertAndFormatCurrency()
    {
        $amount = '100';
        $includeContainer = true;
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION;

        $result = '100.0 grn';

        $this->priceCurrency->expects($this->once())
            ->method('convertAndFormat')
            ->with($amount, $includeContainer, $precision)
            ->willReturn($result);

        $this->assertEquals($result, $this->model->convertAndFormatCurrency($amount, $includeContainer, $precision));
    }

    public function testRender()
    {
        $amountRender = $this->createMock(Amount::class);
        $arguments = ['argument_two' => 2];
        $mergedArguments = ['argument_one' => 1, 'argument_two' => 2];
        $renderText = 'amount data';

        $this->model->expects($this->at(0))
            ->method('getData')
            ->willReturn($this->data);
        $this->model->expects($this->at(1))
            ->method('setData')
            ->with($mergedArguments);
        $this->model->expects($this->at(2))
            ->method('apply')
            ->willReturn($renderText);
        $this->model->expects($this->at(3))
            ->method('setData')
            ->with($this->data);

        $result = $this->model->render($amountRender, $arguments);
        $this->assertEquals($renderText, $result);
    }

    public function testGetAmountRender()
    {
        $amountRender = $this->createMock(Amount::class);
        $this->model->expects($this->at(0))
            ->method('getData')
            ->willReturn($this->data);
        $this->model->render($amountRender);
        $this->assertEquals($amountRender, $this->model->getAmountRender());
    }

    public function testGetPriceType()
    {
        $amountRender = $this->createMock(Amount::class);
        $price = $this->getMockForAbstractClass(PriceInterface::class);
        $sealableItem = $this->getMockForAbstractClass(SaleableInterface::class);
        $priceInfo = $this->createMock(Base::class);
        $priceCode = 'regular_price';

        $amountRender->expects($this->once())
            ->method('getSaleableItem')
            ->willReturn($sealableItem);
        $sealableItem->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $priceInfo->expects($this->once())
            ->method('getPrice')
            ->with($priceCode)
            ->willReturn($price);

        $this->model->expects($this->at(0))
            ->method('getData')
            ->willReturn($this->data);
        $this->model->render($amountRender);
        $this->assertEquals($price, $this->model->getPriceType($priceCode));
    }

    public function testGetPrice()
    {
        $price = 100;
        $amountRender = $this->createMock(Amount::class);
        $amountRender->expects($this->once())
            ->method('getPrice')
            ->with()
            ->willReturn($price);

        $this->model->expects($this->at(0))
            ->method('getData')
            ->willReturn($this->data);
        $this->model->render($amountRender);
        $this->assertEquals($price, $this->model->getPrice());
    }

    public function testGetSealableItem()
    {
        $sealableItem = $this->getMockForAbstractClass(SaleableInterface::class);
        $amountRender = $this->createMock(Amount::class);
        $amountRender->expects($this->once())
            ->method('getSaleableItem')
            ->with()
            ->willReturn($sealableItem);

        $this->model->expects($this->at(0))
            ->method('getData')
            ->willReturn($this->data);
        $this->model->render($amountRender);
        $this->assertEquals($sealableItem, $this->model->getSaleableItem());
    }

    public function testGetAdjustment()
    {
        $amountRender = $this->createMock(Amount::class);
        $adjustment = $this->getMockForAbstractClass(AdjustmentInterface::class);
        $sealableItem = $this->getMockForAbstractClass(SaleableInterface::class);
        $priceInfo = $this->createMock(Base::class);
        $adjustmentCode = 'tax';

        $amountRender->expects($this->once())
            ->method('getSaleableItem')
            ->willReturn($sealableItem);
        $sealableItem->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);
        $priceInfo->expects($this->once())
            ->method('getAdjustment')
            ->with($adjustmentCode)
            ->willReturn($adjustment);

        $this->model->expects($this->at(0))
            ->method('getData')
            ->willReturn($this->data);
        $this->model->expects($this->once())
            ->method('getAdjustmentCode')
            ->willReturn($adjustmentCode);
        $this->model->render($amountRender);
        $this->assertEquals($adjustment, $this->model->getAdjustment());
    }

    public function testFormatCurrency()
    {
        $amount = 5.3456;
        $includeContainer = false;
        $precision = 3;

        $expected = 5.346;

        $this->priceCurrency->expects($this->once())
            ->method('format')
            ->with($amount, $includeContainer, $precision)
            ->willReturn($expected);

        $result = $this->model->formatCurrency($amount, $includeContainer, $precision);
        $this->assertEquals($expected, $result, 'formatCurrent returned unexpected result');
    }
}
