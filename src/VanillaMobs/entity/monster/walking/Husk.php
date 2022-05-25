<?php

namespace VanillaMobs\entity\monster\walking;

use pocketmine\item\ItemIds;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\event\entity\{EntityDamageByEntityEvent, EntityDamageEvent};
use pocketmine\entity\Effect;

use pocketmine\item\Item;
use VanillaMobs\entity\monster\WalkingMonster;

class Husk extends WalkingMonster{
	const NETWORK_ID = 47;

    public $width = 1;
    public $height = 1;
	public $dropExp = [1, 3];
	protected $attackDelay = 0;


	public function getName() : string{
		return "Отброс";
	}

    public function initEntity() : void{
	  parent::initEntity();
	  if(mt_rand(1, 4) == 1){
		  $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY);
		  $this->setScale(0.5);
	  }
	  $this->setMaxHealth(24);
	  $this->setHealth(24);
  }

  public function isBaby() : bool{
	  return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY);
  }

	/**
	 * @param Player $player
	 * @return void
	 */
	public function spawnTo(Player $player) : void{
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

	/**
	 * @param $tickDiff
	 * @param $EnchantL
	 */
	public function entityBaseTick($tickDiff = 1, $EnchantL = 0) {
		if($this->isClosed() or !$this->isAlive()){
			return parent::entityBaseTick($tickDiff, $EnchantL);
		}
		
		if($this->isMorph) {
			return true;
		}

		$hasUpdate = parent::entityBaseTick(1, $EnchantL);
		if($this->attackDelay > 10){
			$ev = new EntityDamageByEntityEvent($this, $this->isnear, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 3);
			$this->isnear->attack($ev->getFinalDamage(), $ev);
			$this->isnear->addEffect(Effect::getEffect(Effect::HUNGER)->setDuration(30 * 20));
			$this->attackDelay = 0;
		}
		return $hasUpdate;
	}

	/**
	 * @return void
	 */
	public function processMove() : void
	{
		parent::processMove();
		$isTarget = false;
		$entities2 = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 1, $this->y - 1, $this->z - 1, $this->x + 1, $this->y + 1, $this->z + 1));
		$entities = $this->getLevel()->getNearbyEntities(new AxisAlignedBB($this->x - 8, $this->y - 8, $this->z - 8, $this->x + 8, $this->y + 8, $this->z + 8));
		for ($i = 0; $i < sizeof($entities); $i++) {
			if ($entities[$i] instanceof Player && $entities[$i]->isSurvival()) {
				$this->target = $entities[$i];
				$isTarget = true;
				$this->isnear = null;
			}
		}
		for ($i = 0; $i < sizeof($entities2); $i++) {
			if ($entities2[$i] instanceof Player && $entities2[$i]->isSurvival()) {
				$this->attackDelay++;
				$this->isnear = $entities2[$i];
				$isTarget = true;
			}
		}
		if (!$isTarget && $this->target instanceof Player) {
			$this->target = null;
			$this->isnear = null;
		}
		$this->defaultMove();
	}

	/**
	 * @return array|Item[]
	 */
	public function getDrops() : array{
	  return array(
      Item::get(ItemIds::ROTTEN_FLESH, 0, mt_rand(0, 2)),
      Item::get(ItemIds::CARROT, 0, mt_rand(0, 1)),
      Item::get(ItemIds::POTATO, 0, mt_rand(0, 1))
	  );
  }
}
