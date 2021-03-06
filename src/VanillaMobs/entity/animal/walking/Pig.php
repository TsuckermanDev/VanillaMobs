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

        if(mt_rand(1, 10) == 1){
            $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY, true);
            $this->setScale(0.5);
        }
        $this->setMaxHealth(10);
        $this->setHealth(10);
    }
public function getSpeed(){
return $this->isAgitation() ? $this->speed * 2 : $this->speed;
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

  public function processMove(){
parent::processMove();
    $isTarget = false;
    $entities = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 7, $this->y - 3, $this->z - 7, $this->x + 7, $this->y + 3, $this->z + 7));
    foreach($entities as $entity){
      if($entity instanceof Player){
        if($entity->getInventory()->getItemInHand()->getId() === 391){
          $this->setRandomPosition(null);
          $this->setTarget($entity);
          $isTarget = true;
          break;
        }
      }
    }
    if($isTarget === false){
      if($this->target instanceof Player){
        $this->setTarget(null);
      }
    }
$this->defaultMove();
  }

  public function getDrops(){
          if($this->isBaby()) return [];
		return [
     Item::get($this->isOnFire() ? Item::COOKED_PORKCHOP : Item::RAW_PORKCHOP, 0, mt_rand(1, 3))
		];
  }
}
