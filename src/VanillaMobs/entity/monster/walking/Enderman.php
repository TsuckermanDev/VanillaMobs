<?php

namespace VanillaMobs\entity\monster\walking;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\{AddEntityPacket, EntityEventPacket, MobEquipmentPacket};
use pocketmine\entity\Entity;
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};
use pocketmine\nbt\tag\{CompoundTag, ListTag, DoubleTag, FloatTag};
use pocketmine\network\Network;
use pocketmine\math\{Vector3, AxisAlignedBB};
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\item\{Bow, Item};
use VanillaMobs\entity\monster\WalkingMonster;

class Enderman extends WalkingMonster{
  const NETWORK_ID = 38;

  

    public $width = 1;
    public $height = 3;
public $dropExp = [1, 3];
  protected $attackDelay = 0;
  protected $randomtp = 0;
  protected $teleport = 0;
  protected $pickblock = 0;
  protected $damage;
  protected $isangry;


  public function getName(){
    return "Странник края";
  }
    public function initEntity(){
        parent::initEntity();

        $this->setMaxHealth(40);
        $this->setHealth(40);
    }
  public function getSpeed(){
  return $this->isangry ? 0.2 : 0.1;
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

		$hasUpdate = parent::entityBaseTick( 1,  $EnchantL);
if($this->attackDelay > 5){
                $ev = new EntityDamageByEntityEvent($this, $this->nearby, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 7);
            $this->nearby->attack($ev->getFinalDamage(), $ev);
     $this->attackDelay = 0;
}
		return $hasUpdate;
	}
  public function processMove(){
  parent::processMove();
    $arraybl = array(8, 9, 10, 11);
    if($this->insideOfWater() || ($this->getLevel()->getWeather()->getWeather() == 1 and $this->getLevel()->getBiomeId($this->x, $this->z) != 2 and !$this->hasHeadBlock())){
   $ev = new EntityDamageByEntityEvent($this, $this, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 1);
            $this->attack($ev->getFinalDamage(), $ev);
    $x = $this->x + rand(-8, 8);
    $z = $this->z + rand(-8, 8);
    $y = $this->level->getHighestBlockAt($x, $z);
    $this->teleport(new Vector3($x, $y + 1.5, $z), $this->yaw, $this->pitch);
    return;
}
    $this->randomtp++;
    $this->pickblock++;

    if($this->teleport == 1){
    $x = $this->x + rand(-8, 8);
    $z = $this->z + rand(-8, 8);
    $y = $this->level->getHighestBlockAt($x, $z);
    $this->teleport(new Vector3($x, $y + 1.5, $z), $this->yaw, $this->pitch);
    $this->teleport = 0;
   }elseif(floor($this->pickblock) == 1000){
   $block = $this->getLevel()->getBlock(new Vector3($this->x, $this->y - 1, $this->z))->getId();
$blockd = $this->getLevel()->getBlock(new Vector3($this->x, $this->y - 1, $this->z))->getDamage();

    $arrayids = array(1, 2, 3, 4, 5, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 24, 35, 37, 38, 39, 40, 41, 42, 43, 46, 47, 48, 56, 57, 58, 73, 74, 79, 81, 82, 86, 87, 88, 89, 91, 98, 99, 100, 103);
    if(in_array($block, $arrayids)){
    $this->setDataProperty (self::DATA_ENDERMAN_HELD_ITEM_ID, self::DATA_TYPE_SHORT, $block);
   $this->setDataProperty (self::DATA_ENDERMAN_HELD_ITEM_DAMAGE, self::DATA_TYPE_SHORT, $blockd);
    $this->getLevel()->setBlock(new Vector3($this->x, $this->y - 1, $this->z), Block::get(0, 0));
    }else{
     $this->pickblock = 0;
    }

   }elseif(floor($this->pickblock) == 2000){
   
    $block = $this->getDataProperty(self::DATA_ENDERMAN_HELD_ITEM_ID, self::DATA_TYPE_SHORT);
   $blockd = $this->getDataProperty(self::DATA_ENDERMAN_HELD_ITEM_DAMAGE, self::DATA_TYPE_SHORT);
    $this->getLevel()->setBlock(new Vector3($this->x + 1, $this->y, $this->z + 1), Block::get($block, $blockd));
$this->setDataProperty (self::DATA_ENDERMAN_HELD_ITEM_ID, self::DATA_TYPE_SHORT, 0);
$this->setDataProperty(self::DATA_ENDERMAN_HELD_ITEM_DAMAGE, self::DATA_TYPE_SHORT, 0);
    $this->pickblock = 0;
   }elseif($this->randomtp > 1500){
    $x = $this->x + rand(-8, 8);
    $z = $this->z + rand(-8, 8);
    $y = $this->level->getHighestBlockAt($x, $z);
    $this->teleport(new Vector3($x, $y + 1.5, $z), $this->yaw, $this->pitch);
    $this->randomtp = 0;
    }

    $isTarget = false;
    $entities2 = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 1, $this->y - 1, $this->z - 1, $this->x + 1, $this->y + 1, $this->z + 1));
    $entities = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 10, $this->y - 10, $this->z - 10, $this->x + 10, $this->y + 10, $this->z + 10));
    foreach($entities as $entity){
      if($entity instanceof Player){
       if($entity->isSurvival()){
      if($this->distance($entity) < 7){
     $isTarget = true;
      }else{
      $this->isangry = false;
      $this->damage = false;
      }
      if($this->damage == true || $this->lookAtEnderman($entity) || $this->isangry == true){
      $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ANGRY, true);
       $this->setTarget($entity);
      $this->isangry = true;
       $this->setNearPlayer(null);
          }
        }
      }
    }
     foreach($entities2 as $entity2){
      if($entity2 instanceof Player){
       if($entity2->isSurvival()){
      if($this->distance($entity2) < 7){
     $isTarget = true;
      }else{
     $this->isangry = false;
     $this->damage = false;
     }

      if($this->damage == true || $this->lookAtEnderman($entity2) || $this->isangry == true){
      $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ANGRY, true);
       $this->attackDelay++;
       $this->setNearPlayer($entity2);
        $this->isangry = true;
          }
        }
      }
    }
    if($isTarget === false){
      if($this->target instanceof Player){
      $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ANGRY, false);
        $this->setTarget(null);
        $this->setNearPlayer(null);
      }
    }
$this->defaultMove();
  }
  public function attack($damage, EntityDamageEvent $source){
    parent::attack($damage, $source);
       if($source instanceof EntityDamageByEntityEvent){
  			$damager = $source->getDamager();
       $entity = $source->getEntity();
   if($entity instanceof Enderman){
    if($damager instanceof Player){
     $this->damage = true;
    }
    if($source->getCause() === EntityDamageEvent::CAUSE_PROJECTILE){
    $source->setCancelled();
    $this->teleport = 1;
       }
     }
    }
  }

	public function lookAtEnderman($entity) : bool{
		if($entity instanceof Player){
			if($entity->getInventory()->getHelmet()->getId() == 86){
				return false;
			}
		}
		$horizontal = sqrt(($this->x - $entity->x) ** 2 + ($this->z - $entity->z) ** 2);
		$vertical = ($this->y + 1) - $entity->y;
		$pitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down
   
 		$xDist = $this->x - $entity->x;
		$zDist = $this->z - $entity->z;
		$yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($yaw < 0){
			$yaw += 360.0;
		}

    $yaw1 = $yaw / 1.2;
    $yaw2 = $yaw * 1.2;

   if($pitch > 0){
    $pitch1 = $pitch / 1.4;
    $pitch2 = $pitch * 1.4;
    }else{
     $pitch1 = $pitch * 1.4;
    $pitch2 = $pitch / 1.4;
    }

		if($entity->yaw > $yaw1 and $entity->yaw < $yaw2 and ($entity->pitch >= $pitch1 and $entity->pitch <= $pitch2 || $pitch == 0)){
    return true;
   }else{
   return false;
  }
	}
  public function getDrops(){
   
   $get = $this->getDataProperty(self::DATA_ENDERMAN_HELD_ITEM_ID, self::DATA_TYPE_SHORT);
   $getd = $this->getDataProperty(self::DATA_ENDERMAN_HELD_ITEM_DAMAGE, self::DATA_TYPE_SHORT);
    if($get == null){
     $get = 0;
     }
     if($getd == null){
     $getd = 0;
     }
    
    return [
      Item::get(Item::ENDER_PEARL, 0, mt_rand(0, 1)),
      Item::get($get, $getd, 1)
    ];

  }
}
