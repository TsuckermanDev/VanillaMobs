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
use pocketmine\item\{Bow, Item};
use VanillaMobs\entity\monster\WalkingMonster;

class Skeleton extends WalkingMonster{
  const NETWORK_ID = 34;

  

    public $width = 1;
    public $height = 2;
public $dropExp = [1, 3];
  protected $attackDelay = 0;


  public function getName(){
    return "Скелет";
  }
    public function initEntity(){
        parent::initEntity();

        $this->setMaxHealth(20);
        $this->setHealth(20);
    }
  
public function getSpeed(){
return 0.1;
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
       $pkk = new MobEquipmentPacket();
        $pkk->eid = $this->getId();
        $pkk->item = new Bow();
        $pkk->slot = 0;
        $pkk->selectedSlot = 0;
        $player->dataPacket($pkk);

		parent::spawnTo($player);
	}

     
  public function processMove(){
    parent::processMove();
    $this->onSun();
if($this->attackDelay > 60 and $this->nearby != null){
            $arrow = Entity::createEntity("Arrow", $this->level, $this->nbtShoot(), $this);
            $arrow->spawnToAll();
      $this->attackDelay = 0;


}
    $isTarget = false;
    $entities = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 10, $this->y - 10, $this->z -10, $this->x + 10, $this->y + 10, $this->z + 10));
    foreach($entities as $entity){
      if($entity instanceof Player){
       if($entity->isSurvival()){

        if($this->distance($entity) < 10 and $this->distance($entity) > 5){
$this->setDataProperty(self::DATA_TARGET_EID, self::DATA_TYPE_LONG, null);
          $this->setNearPlayer(null);
          $this->setRandomPosition(null);
          $this->setTarget($entity);
          $isTarget = true;
          break;
        }
      if($this->distance($entity) < 5){
             $this->attackDelay++;
          $this->setTarget(null);
          $this->setNearPlayer($entity);
          $isTarget = true;
$this->setDataProperty(self::DATA_TARGET_EID, self::DATA_TYPE_LONG, $this->nearby->getId());
          break;
          }
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
      Item::get(Item::BONE, 0, mt_rand(0, 2)),
      Item::get(Item::ARROW, 0, mt_rand(0, 2))
    ];

  }
}
