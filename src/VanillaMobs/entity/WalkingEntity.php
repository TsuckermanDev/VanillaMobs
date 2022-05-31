<?php

namespace VanillaMobs\entity;

use pocketmine\entity\Creature;
use pocketmine\math\{Math, Vector3};
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\block\Block;
use pocketmine\network\Network;
use pocketmine\level\particle\BubbleParticle;
use pocketmine\entity\{Player, Entity, Living};
use pocketmine\math\AxisAlignedBB;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\Server;

abstract class WalkingEntity extends BaseEntity{
	protected $target = null;
	protected $nearby = null;
  protected $random = null;
	protected $knockback;
	protected $agitation;
	protected $damager = null;
	protected $collide;
  protected $speed = 0.0515;
	protected $collider = null;
  protected $move;
	protected $gravity = 0.08;
  protected $inWaterTicks;

	public function entityBaseTick($tickDiff = 1, $EnchantL = 0) {
		if($this->isClosed() or !$this->isAlive()){
			return parent::entityBaseTick($tickDiff, $EnchantL);
		}
		
		if($this->isMorph) {
			return true;
		}
		$hasUpdate = parent::entityBaseTick(1, $EnchantL);
		$this->processMove();
		return $hasUpdate;
	}
public function isKnockBack() : bool{
return $this->knockback > 0;
}
public function isSwimming() : bool{
return $this->inWaterTicks > 0;
}
public function isCollide() : bool{
return $this->collide > 0;
}
public function isAgitation() : bool{
return $this->agitation > 0;
}
  public function setCollide(){
 $bb = new AxisAlignedBB($this->x - 0.15, $this->y - 0.15, $this->z - 0.15, $this->x + 0.15, $this->y + 0.15, $this->z + 0.15);
   foreach($this->getLevel()->getCollidingEntities($bb->expandedCopy(0.25, 0.25, 0.25), $this) as $key){
   if($key instanceof Living){
  $this->collide = 3;
  $this->setCollider($key);
    }
  }
}
public function setCollider($key){
$this->collider = $key;
}
public function setRandomPosition($value){
$this->random = $value;
}
public function setTarget($value){
$this->target = $value;
}
public function setNearPlayer($value){
$this->nearby = $value;
}
public function moveToTarget($target){
     $this->move = true;
      $finalX = $target->x - $this->x;
      $finalZ = $target->z - $this->z;
      if($finalX ** 2 + $finalZ ** 2 < 2){
        $this->setTarget(null);
        return;
      }
          $diff = abs($finalX) + abs($finalZ);
      $this->motionX = ($this->insideOfWater() ?$this->getSpeed() / 2 : $this->getSpeed()) * (($target->x - $this->x) / $diff);
      $this->motionZ = ($this->insideOfWater() ?$this->getSpeed() / 2 : $this->getSpeed()) * (($target->z - $this->z) / $diff);
      $radius = $this->width / 2;
      		$dx = $this->motionX;
		$dz = $this->motionZ;
		$dy = $this->motionY;

		$newX = Math::floorFloat($this->x + $dx);
		$newZ = Math::floorFloat($this->z + $dz);
		$newY = Math::floorFloat($this->y + $dy);
		$v = $this->getDirectionVector();
		$block = $this->level->getBlock(new Vector3($this->x + $v->x, $this->y, $this->z + $v->z));
		if($block->isSolid()){
			$block = $this->level->getBlock(new Vector3($this->x + $v->x, $this->y + 1, $this->z + $v->z));
     $blocks = array(85, 183, 184, 185, 186, 187);
			if(!$block->isSolid() and !in_array($block->getId(), $blocks)){
				$this->motionY = 0.2;
			}
		}
      $boundingBox = new AxisAlignedBB(round($this->x - $radius + ($this->motionX * 10)), $this->y, round($this->z - $radius + ($this->motionZ * 10)), round($this->x + $radius + ($this->motionX * 10)), ceil($this->y + $this->height), round($this->z + $radius + ($this->motionZ * 10)));


      $collision = $this->getLevel()->getCollisionCubes($this, $boundingBox, false);
      $height = 0;
      foreach($collision as $block){
        $height += ($block->maxY - $block->minY);
      }

      if($height > 1){
        $this->motionX = 0;
        $this->motionZ = 0;
        $this->setTarget(null);
        return;
      }

      $angle = atan2($target->z - $this->z, $target->x - $this->x);
      $this->yaw = (($angle * 180) / M_PI) - 90;
      $this->pitch = 0;
     if($this->isSwimming()){
     $this->motionY = 0.084;
     }
     $this->move($this->motionX, $this->motionY, $this->motionZ);

      $this->getLevel()->addEntityMovement($this->chunk->getX(), $this->chunk->getZ(), $this->id, $this->x, $this->y, $this->z, $this->yaw, $this->pitch);
}
	public function processMove(){
		$this->setCollide();
   if($this->insideOfWater()){
   $this->inWaterTicks = 8;
   }
		if($this->isSwimming()) {
     $this->inWaterTicks--;
}
   if($this->isAgitation()){
    $this->agitation--;
   }
  if($this->isCollide()){
   $this->collide--;
  }
  if($this->isKnockBack()){
  $this->knockback--;
  }
	}
public function moveRandom($to){
      $this->move = true;
      $finalX = $to->x - $this->x;
      $finalZ = $to->z - $this->z;
      if($finalX ** 2 + $finalZ ** 2 < 2){
        $this->setRandomPosition(null);
        return;
      }
          $diff = abs($finalX) + abs($finalZ);
      $this->motionX = ($this->isSwimming() ?$this->getSpeed() / 2 : $this->getSpeed()) * (($to->x - $this->x) / $diff);
      $this->motionZ = ($this->isSwimming() ?$this->getSpeed() / 2 : $this->getSpeed()) * (($to->z - $this->z) / $diff);
      $radius = $this->width / 2;
      		$dx = $this->motionX;
		$dz = $this->motionZ;
		$dy = $this->motionY;

		$newX = Math::floorFloat($this->x + $dx);
		$newZ = Math::floorFloat($this->z + $dz);
		$newY = Math::floorFloat($this->y + $dy);
		$v = $this->getDirectionVector();
		$block = $this->level->getBlock(new Vector3($this->x + $v->x, $this->y, $this->z + $v->z));
		if($block->isSolid()){
			$block = $this->level->getBlock(new Vector3($this->x + $v->x, $this->y + 1, $this->z + $v->z));
     $blocks = array(85, 183, 184, 185, 186, 187);
			if(!$block->isSolid() and !in_array($block->getId(), $blocks)){
				$this->motionY = 0.2;
			}
		}
      $boundingBox = new AxisAlignedBB(round($this->x - $radius + ($this->motionX * 10)), $this->y, round($this->z - $radius + ($this->motionZ * 10)), round($this->x + $radius + ($this->motionX * 10)), ceil($this->y + $this->height), round($this->z + $radius + ($this->motionZ * 10)));


      $collision = $this->getLevel()->getCollisionCubes($this, $boundingBox, false);
      $height = 0;
      foreach($collision as $block){
        $height += ($block->maxY - $block->minY);
      }

      if($height > 1){
        $this->motionX = 0;
        $this->motionZ = 0;
        $this->setRandomPosition(null);
        return;
      }

      $angle = atan2($to->z - $this->z, $to->x - $this->x);
      $this->yaw = (($angle * 180) / M_PI) - 90;
      $this->pitch = 0;
     if($this->isSwimming()){
     $this->motionY = 0.084;
     }
      $this->move($this->motionX, $this->motionY, $this->motionZ);

      $this->getLevel()->addEntityMovement($this->chunk->getX(), $this->chunk->getZ(), $this->id, $this->x, $this->y, $this->z, $this->yaw, $this->pitch);
}

	public function defaultMove(){
       $this->move = false;
       if($this->isKnockBack()){
       $this->move = true;
      $this->mobKnockback($this->damager, 0, $this->x - $this->damager->x, $this->z - $this->damager->z, 0.4);
      return;
       }
      if($this->isCollide()){
       $this->move = true;
       $this->knockback($this->collider, 0, $this->x - $this->collider->x, $this->z - $this->collider->z, 0.2);
       return;
       }
        if($this->nearby instanceof Vector3){
       $this->move = true;
     if($this->isSwimming()){
     $this->motionY = 0.084;
     }
      $angle = atan2($this->nearby->z - $this->z, $this->nearby->x - $this->x);
			$this->yaw = (($angle * 180) / M_PI) - 90;
			$xx = $this->nearby->x - $this->x;
			$yy = $this->nearby->y - $this->y;
			$zz = $this->nearby->y - $this->y;
			$this->pitch = $yy == 0 ? 0 : rad2deg(-atan2($yy, sqrt($xx ** 2 + $zz ** 2)));
        return;
        }
        
        if($this->random instanceof Vector3){
        $this->moveRandom($this->random);
        return;
        }
        if($this->target instanceof Vector3){
        $this->moveToTarget($this->target);
        return;
        }
       if($this->move === false){
				$this->motionX = 0;
				$this->motionZ = 0;
				$rand = mt_rand(1, 150);
				if ($rand === 1) {
					$this->setRandomPosition(new Vector3($this->x + rand(-8, 8), $this->y, $this->z + rand(-8, 8)));
			}	else if ($rand > 1 and $rand < 5) {
					$this->yaw = max(-180, min(180, ($this->yaw + rand(-90, 90))));
				}else if ($rand > 5 and $rand < 10) {
          foreach($this->server->getOnlinePlayers() as $player){
         if($this->distance($player) < 5){
					$this->lookAt($this, $player);
           }
					}
				}
     if($this->isSwimming()){
     $this->motionY = 0.084;
     }
$this->getLevel()->addEntityMovement($this->chunk->getX(), $this->chunk->getZ(), $this->id, $this->x, $this->y, $this->z, $this->yaw, $this->pitch);
				$this->move($this->motionX, $this->motionY, $this->motionZ);
}
	}

  

  public function attack($damage, EntityDamageEvent $source){
    parent::attack($damage, $source);
       if($source instanceof EntityDamageByEntityEvent){
  			$damager = $source->getDamager();
       $entity = $source->getEntity();
    
     if($source->getEntity() instanceof $this){//извините по другому кнокбек не работает блять(( если ты знаешь как сделать по другому напиши мне в вк пж
   $this->damager = $damager;
  $this->knockback = 2;
}

      if($damager instanceof Player){
       if($damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_FIRE_ASPECT)){
      $entity->setOnFire(3);
        }
      }
     
    }
  }
}
