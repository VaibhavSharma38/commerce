<?php

namespace xepan\commerce;

Class page_test1 extends \xepan\base\Page{

	function init(){
		parent::init();		

		$array = [
					'MEH-1-NAVY',
					'MEH-2-SPARKLE WHITE',
					'MEH-3-INK BLUE',
					'MEH-4-AQUA GRAY',
					'MEH-8-AIRFORCE BLUE',
					'MEH-13-SILVER',
					'MEH-14-DULL BLUE',
					'MEH-21-LIGHT BLUE',
					'MEH-22-CARBON GRAY',
					'MEH-23-BROWN',
					'MEH-24-BLUE SHADOW',
					'MEH-25-AQUA GRAY'
				];

		$item_description = "It is an intricately designed modern collection of exquisite hand tufted rugs. This collection brings for you a perfect blend of traditional & modern designing. As the name suggests this collection is inspired by the ageless & traditional mehandi designs artistically created by combining New Zealand wool & Art Silk. The modish touch added to these mehandi designs creates a unique charm & casts a spell on the onlookers.";

		foreach ($array as $item_name) {
			$item_m = $this->add('xepan\commerce\Model_Item');
			$item_m->loadBy('name',$item_name);	
			$item_m['description'] = $item_description;
			$item_m->save();
		}
	}
}

// 'ARB-VNS-002-SILVER'
// 'RN-11-ICE BLUE'