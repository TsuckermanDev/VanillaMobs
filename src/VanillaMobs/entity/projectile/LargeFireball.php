<?php

namespace VanillaMobs\entity\projectile;

use pocketmine\level\Level;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\level\Explosion;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\entity\Projectile;

class LargeFireball extends Projectile{
	const NETWORK_ID = 85;

	public $width = 1.0;
	public $height = 1.0;
	protected $damage = 4;
	protected $drag = 0.01;
	protected $gravity = 0.05;
	protected $isCritical;
	protected $canExplode = false;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, bool $critical = false){
		parent::__construct($level, $nbt, $shootingEntity, $critical);
		$this->isCritical = $critical;
	}

	public function isExplode() : bool{
		return $this->canExplode;
	}

	public function setExplode(bool $bool){
		$this->canExplode = $bool;
	}

	public function entityBaseTick($tickDiff = 1){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->onGround or $this->hadCollision){
				$this->server->getPluginManager()->callEvent($ev = new ExplosionPrimeEvent($this, 2, $dropItem = false));
				if(!$ev->isCancelled()){
                    $explosion = new Explosion($this, $ev->getForce(), $this);
$ev->setBlockBreaking(false);

					$explosion->explodeB();
				}
			
			$this->close();
			$hasUpdate = true;
		}
		
		return $hasUpdate;
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = LargeFireball::NETWORK_ID;
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

}