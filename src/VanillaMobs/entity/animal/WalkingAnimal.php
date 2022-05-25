<?php

namespace VanillaMobs\entity\animal;

use VanillaMobs\entity\{WalkingEntity, BaseEntity};
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};

abstract class WalkingAnimal extends WalkingEntity{

  public function attack($damage, EntityDamageEvent $source){
    parent::attack($damage, $source);
       if($source instanceof EntityDamageByEntityEvent){
  			$damager = $source->getDamager();
     if($source->getEntity() instanceof $this){
if(!$source->getEntity()->isInsideOfWater()){

  $this->agitation = 60;
        }
      }
    }
  }
}
