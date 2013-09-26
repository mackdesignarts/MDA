<?php

namespace Site\FrontendBundle\Billing;

use Site\FrontendBundle\Entity\AdLevel;

class AdAvailabilityReporter { 
	
	private $em;

	public function __construct($em)
	{
		$this->em = $em;
	}

	// returns true if there is slots available
	public function checkCategoryFeatured($market, $category)
	{
		$count = $this->em->getRepository('SiteFrontendBundle:StandardAd')->
			countCategoryFeatured($market, $category)
		;

		$strategy = $this->em->getRepository('SiteFrontendBundle:MarketCategoryPrice')->findOneBy(array(
			'market' => $market,
			'category' => $category
		));

		if($strategy)
		{
			return $count < $strategy->getFeaturedMaxUnits();
		}

		return $count < 8; // @todo implement system fallback
	}

	public function checkMarketFeatured($market)
	{
		$count = $this->em->getRepository('SiteFrontendBundle:StandardAd')->
			countMarketFeatured($market)
		;

		$strategy = $this->em->getRepository('SiteFrontendBundle:MarketPrice')->findOneBy(array(
			'market' => $market
		));

		if($strategy)
		{
			return $count < $strategy->getFeaturedMaxUnits();
		}

		return $count < 8; // @todo implement system fallback
	}

	public function check($market, $category, $adLevel)
	{
		$em = $this->em;

		switch($adLevel->getId())
		{
			case $adLevel::FREE :
				$available = true;
			break;

			case $adLevel::CATEGORY_ENHANCED : 
				$available = true;
			break;

			case $adLevel::CATEGORY_FEATURED : 
				$available = $this->checkCategoryFeatured($market, $category); 
			break;

			case $adLevel::MARKET_FEATURED :
				$available = $this->checkMarketFeatured($market); 
			break;
		}

		return $available;
	}
}
