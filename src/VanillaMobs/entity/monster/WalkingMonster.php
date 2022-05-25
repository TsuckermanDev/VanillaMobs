<?php

namespace VanillaMobs\entity\monster;

use VanillaMobs\entity\{WalkingEntity, BaseEntity};
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};
use pocketmine\level\Level;
use pocketmine\math\Vector3;

abstract class WalkingMonster extends WalkingEntity{
      public function onSun(){
         		$time = $this->getLevel() !== null ? $this->getLevel()->getTime() % Level::TIME_FULL : Level::TIME_NIGHT;
		if((!$this->isInsideOfWater()) && ($time < Level::TIME_NIGHT || $time > Level::TIME_SUNRISE) && (!$this->hasHeadBlock())){
			$this->setOnFire(1);
		}
  }
	public function hasHeadBlock($height = 50): bool{
		$x = floor($this->getX());
		$y = floor($this->getY()) + 2;
		$z = floor($this->getZ());
		$m = false;
		for($i=$y; $i < $y + $height; $i++){
			$block = $this->getLevel()->getBlock(new Vector3($x, $i, $z));
			if($block->getId() != 0){
				$m = true;
			}
		}
		return $m;
	}
}
