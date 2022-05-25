<?php

namespace VanillaMobs\entity\animal\walking;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\Network;
use pocketmine\math\AxisAlignedBB;
use pocketmine\item\Item;
use pocketmine\level\sound\PopSound;
use VanillaMobs\entity\animal\WalkingAnimal;

class Chicken extends WalkingAnimal{
  const NETWORK_ID = 10;

  
	const DROP_EGG_DELAY_MIN = 6000;
	const DROP_EGG_DELAY_MAX = 12000;
	private $dropEggTimer = 0;
	private $dropEggTime = 0;

    public $width = 1;
    public $height = 1;
public $dropExp = [1, 3];


  public function getName(){
    return "Курица";
  }
    public function initEntity(){
        parent::initEntity();

        $this->setMaxHealth(4);
        $this->setHealth(4);
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
		if($this->dropEggTime === 0){
			$this->dropEggTime = mt_rand(self::DROP_EGG_DELAY_MIN, self::DROP_EGG_DELAY_MAX);
		}

		if($this->dropEggTimer >= $this->dropEggTime){ //срём нахуй:D!
			$this->layEgg();
		}else{
			$this->dropEggTimer += $tickDiff;
		}

		parent::entityBaseTick($tickDiff);
		return true;
	}
	private function layEgg(){
		$item = Item::get(Item::EGG, 0, 1);
		$this->getLevel()->dropItem($this, $item);
		$this->getLevel()->addSound(new PopSound($this), $this->getViewers());

		$this->dropEggTimer = 0;
		$this->dropEggTime = 0;
	}
  public function processMove(){
    parent::processMove();
    $isTarget = false;
    $entities = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 7, $this->y - 3, $this->z - 7, $this->x + 7, $this->y + 3, $this->z + 7));
    foreach($entities as $entity){
      if($entity instanceof Player){
        if($entity->getInventory()->getItemInHand()->getId() === 295){
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
      Item::get(Item::FEATHER, 0, mt_rand(1, 2)),
      Item::get(Item::COOKED_CHICKEN, 0, mt_rand(0, 1))
    ];
  }else{
     return [
      Item::get(Item::FEATHER, 0, mt_rand(1, 2)),
      Item::get(Item::RAW_CHICKEN, 0, mt_rand(0, 1))
    ];
    }
  }
}
