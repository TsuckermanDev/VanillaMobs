<?php

namespace VanillaMobs\entity\animal\walking;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\Network;
use pocketmine\math\AxisAlignedBB;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\level\particle\BubbleParticle;
use VanillaMobs\entity\animal\WalkingAnimal;

class Pig extends WalkingAnimal{
  const NETWORK_ID = 12;

  

    public $width = 1;
    public $height = 1;
public $dropExp = [1, 3];


  public function getName(){
    return "Свинья";
  }
    public function initEntity(){
        parent::initEntity();

        $this->setMaxHealth(10);
        $this->setHealth(10);
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

  public function processMove(){
parent::processMove();
    $isTarget = false;
    $entities = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 7, $this->y - 3, $this->z - 7, $this->x + 7, $this->y + 3, $this->z + 7));
    foreach($entities as $entity){
      if($entity instanceof Player){
        if($entity->getInventory()->getItemInHand()->getId() === 391){
          $this->target = $entity;
          $isTarget = true;
          break;
        }
      }
    }
    if($isTarget === false){
      if($this->target instanceof Player){
        $this->target = null;
      }
    }
$this->defaultMove();
  }

  public function getDrops(){
    if($this->isOnFire()){
    return [
      Item::get(Item::COOKED_PORKCHOP, 0, mt_rand(1, 3))
    ];
    }else{
    return [
      Item::get(Item::RAW_PORKCHOP, 0, mt_rand(1, 3))
    ];
    }
  }
}
