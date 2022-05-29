<?php

namespace VanillaMobs\entity\monster\walking;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\{AddEntityPacket, EntityEventPacket, MobEquipmentPacket};
use pocketmine\entity\Entity;
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent, EntityShootBowEvent};
use pocketmine\nbt\tag\{CompoundTag, ListTag, DoubleTag, FloatTag};
use pocketmine\network\Network;
use pocketmine\math\{Vector3, AxisAlignedBB};
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\item\Item;
use VanillaMobs\entity\monster\WalkingMonster;


class Zombie extends WalkingMonster{
  const NETWORK_ID = 32;

  

    public $width = 1;
    public $height = 2;
public $dropExp = [1, 3];
  protected $attackDelay = 0;


  public function getName(){
    return "Зомби";
  }
    public function initEntity(){
        parent::initEntity();

        if(mt_rand(1, 4) == 1){
            $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY, true);
            $this->setScale(0.5);
        }
        $this->setMaxHealth(24);
        $this->setHealth(24);
    }

public function getSpeed(){
return $this->isBaby() ? 0.3 : 0.1;
}
  public function isBaby() : bool{
    return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY);
 }

	public function spawnTo(Player $player){
 
    

		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = self::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);


		parent::spawnTo($player);
	}
       
     public function entityBaseTick($tickDiff = 1, $EnchantL = 0) {
		if($this->isClosed() or !$this->isAlive()){
			return parent::entityBaseTick($tickDiff, $EnchantL);
		}
		
		if($this->isMorph){
			return true;
		}

		$hasUpdate = parent::entityBaseTick(1, $EnchantL);
if($this->attackDelay > 10){

                $ev = new EntityDamageByEntityEvent($this, $this->nearby, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 3);
            $this->nearby->attack($ev->getFinalDamage(), $ev);
     $this->attackDelay = 0;
}
		return $hasUpdate;
	}
  public function processMove(){
parent::processMove();
    $this->onSun();

    $isTarget = false;
    $entities2 = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 1, $this->y - 1, $this->z - 1, $this->x + 1, $this->y + 1, $this->z + 1));
    $entities = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 8, $this->y - 8, $this->z - 8, $this->x + 8, $this->y + 8, $this->z + 8));
    foreach($entities as $entity){
      if($entity instanceof Player){
       if($entity->isSurvival()){
       $this->setRandomPosition(null);
       $this->setNearPlayer(null);
       $this->setTarget($entity);
       $isTarget = true;

        }
      }
    }
     foreach($entities2 as $entity2){
      if($entity2 instanceof Player){
       if($entity2->isSurvival()){
       $this->attackDelay += 1;
       $this->setRandomPosition(null);
       $this->setTarget(null);
       $this->setNearPlayer($entity2);
       $isTarget = true;
        }
      }
    }
    if($isTarget === false){
      if($this->target instanceof Player || $this->nearby instanceof Player){
        $this->setTarget(null);
        $this->setNearPlayer(null);
      }
    }
    $this->defaultMove();
  }

  public function getDrops(){
    return [
      Item::get(Item::ROTTEN_FLESH, 0, mt_rand(0, 2)),
      Item::get(Item::CARROT, 0, mt_rand(0, 1)),
      Item::get(Item::POTATO, 0, mt_rand(0, 1))
    ];

  }
}
