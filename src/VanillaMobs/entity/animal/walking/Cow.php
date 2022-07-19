<?php

namespace VanillaMobs\entity\animal\walking;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\Network;
use pocketmine\math\AxisAlignedBB;
use pocketmine\item\Item;
use VanillaMobs\entity\animal\WalkingAnimal;

final class Cow extends WalkingAnimal{
    const NETWORK_ID = 11;

    public int $width = 1;
    public int $height = 1;
    public array $dropExp = [1, 3];

    public function getName() : string{
        return "Корова";
    }

    public function initEntity() : void{
        parent::initEntity();
        if (mt_rand(1, 10) == 1) {
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

    public function spawnTo(Player $player) {
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
        for ($i = 0, $i < sizeof($entries), $i++) {
            if ($entries[$i] instanceof Player) {
                if ($entries[$i]->getInventory()->getItemInHand()->getId() === 296) {
                    $this->setRandomPosition(null);
                    $this->setTarget($entries[$i]);
                    $isTarget = true;
                    break;
                }
            }
        }
        if (!$isTarget) {
            if ($this->target instanceof Player) {
                $this->setTarget(null);
            }
        }
        $this->defaultMove();
    }

    public function getDrops() {
      if ($this->isBaby()) return [];
      return [
        Item::get(Item::LEATHER, 0, mt_rand(0, 2)),
        Item::get($this->isOnFire() ? Item::COOKED_BEEF : Item::RAW_BEEF, 0, mt_rand(1, 3))
    ];
  }
}
