<?php

namespace VanillaMobs\entity;

use pocketmine\entity\Creature;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\block\Block;
use pocketmine\network\Network;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\entity\{Player, Entity};
use pocketmine\math\AxisAlignedBB;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\Server;

abstract class WalkingEntity extends BaseEntity{
 protected $target = null;
  protected $isnear = null;
  protected $knockback;
  protected $agitation;
  protected $damager = null;


  protected $gravity = 0.17;
public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
		if($this->isClosed() or !$this->isAlive()){
			return parent::entityBaseTick($tickDiff, $EnchantL);
		}
		
		if($this->isMorph){
			return true;
		}

		$hasUpdate = parent::entityBaseTick(1, $EnchantL);
      $this->processMove();
		return $hasUpdate;
	}
  
    
  public function processMove(){

if(!$this->chunk->isGenerated()){
            $this->chunk->setGenerated();
        }
        if(!$this->chunk->isPopulated()){
            $this->chunk->setPopulated();
        }

}
  public function defaultMove(){
if(!$this->isInsideOfWater()){
if($this->agitation > 0){
   $this->isnear = null;
     $rand = mt_rand(1, 20);
      if($rand === 5){
        
        $this->target = new Vector3($this->x + rand(-8, 8), $this->y, $this->z + rand(-8, 8));
}
if($this->target instanceof Vector3){
      $xDiff = $this->target->x - $this->x;
      $zDiff = $this->target->z - $this->z;
      if($xDiff ** 2 + $zDiff ** 2 < 2){
        $this->target = null;
        return;
      }
      $diff = abs($xDiff) + abs($zDiff);
      $speed = 0.12;
      
      $this->motionX = $speed * (($this->target->x - $this->x) / $diff);
      $this->motionZ = $speed * (($this->target->z - $this->z) / $diff);

      $radius = $this->width / 2;

      $boundingBox = new AxisAlignedBB(round($this->x - $radius + ($this->motionX * 10)), $this->y, round($this->z - $radius + ($this->motionZ * 10)), round($this->x + $radius + ($this->motionX * 10)), ceil($this->y + $this->height), round($this->z + $radius + ($this->motionZ * 10)));

      $block = $this->getLevel()->getBlock($this->getSide(0));
      if(!$block->isSolid()){
       $this->motionY -= $this->gravity;
      }else{
        $this->motionY = 0;
      }

      $collision = $this->getLevel()->getCollisionCubes($this, $boundingBox, false);
      $height = 0;
      foreach($collision as $block){
        $height += ($block->maxY - $block->minY);
      }

      if($height > 1){
        $this->motionX = 0;
        $this->motionZ = 0;
        $this->target = null;
        return;
      }elseif($height > 0){
        $this->motionY = 0.25;
      }

      $angle = atan2($this->target->z - $this->z, $this->target->x - $this->x);
      $this->yaw = (($angle * 180) / M_PI) - 90;
      $this->pitch = 0;

      $this->move($this->motionX, $this->motionY, $this->motionZ);

      $this->getLevel()->addEntityMovement($this->chunk->getX(), $this->chunk->getZ(), $this->id, $this->x, $this->y, $this->z, $this->yaw, $this->pitch);

}
$this->agitation--;
}elseif($this->knockback > 1 and $this->damager instanceof Vector3){
$this->target = null;
$this->isnear = null;
     $base = 0.4;
     $x = $this->x - $this->damager->x;
     $z = $this->z - $this->damager->z;
   		$f = sqrt($x * $x + $z * $z);
		if($f <= 0){
			return;
		}
     			$f = 1 / $f;

			$motion = new Vector3($this->motionX, $this->motionY, $this->motionZ);

			$motion->x /= 2;
			$motion->y /= 2;
			$motion->z /= 2;
			$motion->x += $x * $f * $base;
			$motion->y += $base;
			$motion->z += $z * $f * $base;

			if($motion->y > $base){
				$motion->y = $base;
			}

			$this->setMotion($motion);
$this->knockback -= 1;
}elseif($this->isnear instanceof Vector3){
         
$angle = atan2($this->isnear->z - $this->z, $this->isnear->x - $this->x);
      $this->yaw = (($angle * 180) / M_PI) - 90;
      $xx = $this->isnear->x - $this->x;
      $yy = $this->isnear->y - $this->y;
      $zz = $this->isnear->y - $this->y;
$this->pitch = $yy == 0 ? 0 : rad2deg(-atan2($yy, sqrt($xx ** 2 + $zz ** 2)));
 
      
    }elseif($this->target instanceof Vector3){

      $xDiff = $this->target->x - $this->x;
      $zDiff = $this->target->z - $this->z;
      if($xDiff ** 2 + $zDiff ** 2 < 2){
        $this->target = null;
        return;
      }
      $diff = abs($xDiff) + abs($zDiff);
      $speed = 0.08;
      
      $this->motionX = $speed * (($this->target->x - $this->x) / $diff);
      $this->motionZ = $speed * (($this->target->z - $this->z) / $diff);

      $radius = $this->width / 2;

      $boundingBox = new AxisAlignedBB(round($this->x - $radius + ($this->motionX * 10)), $this->y, round($this->z - $radius + ($this->motionZ * 10)), round($this->x + $radius + ($this->motionX * 10)), ceil($this->y + $this->height), round($this->z + $radius + ($this->motionZ * 10)));

      $block = $this->getLevel()->getBlock($this->getSide(0));
      if(!$block->isSolid()){
       $this->motionY -= $this->gravity;
      }else{
        $this->motionY = 0;
      }

      $collision = $this->getLevel()->getCollisionCubes($this, $boundingBox, false);
      $height = 0;
      foreach($collision as $block){
        $height += ($block->maxY - $block->minY);
      }

      if($height > 1){
        $this->motionX = 0;
        $this->motionZ = 0;
        $this->target = null;
        return;
      }elseif($height > 0){
        $this->motionY = 0.25;
      }

      $angle = atan2($this->target->z - $this->z, $this->target->x - $this->x);
      $this->yaw = (($angle * 180) / M_PI) - 90;
      $this->pitch = 0;

      $this->move($this->motionX, $this->motionY, $this->motionZ);

      $this->getLevel()->addEntityMovement($this->chunk->getX(), $this->chunk->getZ(), $this->id, $this->x, $this->y, $this->z, $this->yaw, $this->pitch);

}else{
      $this->motionX = 0;
      $this->motionZ = 0;
      $rand = mt_rand(1, 150);
      if($rand === 1){
        
        $this->target = new Vector3($this->x + rand(-8, 8), $this->y, $this->z + rand(-8, 8));
      
      }elseif($rand > 1 and $rand < 5){
        $this->yaw = max(-180, min(180, ($this->yaw + rand(-90, 90))));
        $this->getLevel()->addEntityMovement($this->chunk->getX(), $this->chunk->getZ(), $this->id, $this->x, $this->y, $this->z, $this->yaw, $this->pitch);
      }
      if(!$this->getLevel()->getBlock($this->round())->isSolid()){
       $this->motionY -= $this->gravity;
      }else{
        $this->motionY = 0.25;
      }
      $this->move($this->motionX, $this->motionY, $this->motionZ);
    
  }
  }else{
  $this->motionY = 0.2;
			$this->level->addParticle(new BubbleParticle(new Vector3($this->x + rand(-1, 1), $this->y + 0.7, $this->z + rand(-1, 1))));
  }
}
  public function attack($damage, EntityDamageEvent $source){
    parent::attack($damage, $source);
       if($source instanceof EntityDamageByEntityEvent){
  			$damager = $source->getDamager();
       $entity = $source->getEntity();
    
     if($source->getEntity() instanceof $this){//извините по другому кнокбек не работает блять(( если ты знаешь как сделать по другому напиши мне в вк пж
   $this->damager = $damager;
if(!$source->getEntity()->isInsideOfWater()){
  $this->knockback = 3;

  }
}

      if($damager instanceof Player){
       if($damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_FIRE_ASPECT)){
      $entity->setOnFire(3);
        }
      }
     
    }
  }
}
