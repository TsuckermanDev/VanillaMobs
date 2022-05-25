<?php

namespace VanillaMobs\entity\animal\walking;

use pocketmine\Player;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\{ProtocolInfo, InteractPacket, EntityEventPacket};
use pocketmine\nbt\tag\{CompoundTag, ByteTag};
use pocketmine\level\Level;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\Network;
use pocketmine\math\AxisAlignedBB;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\block\Wool;
use VanillaMobs\entity\animal\WalkingAnimal;

class Sheep extends WalkingAnimal{
  const NETWORK_ID = 13;

    public $width = 1;
    public $height = 1;
public $dropExp = [1, 3];

public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->Color)){
			$nbt->Color = new ByteTag("Color", self::getRandomColor());
		}
		parent::__construct($level, $nbt);

		$this->setDataProperty(self::DATA_COLOR, self::DATA_TYPE_BYTE, $this->getColor());
	}
    public function initEntity(){
        parent::initEntity();

        if(mt_rand(1, 4) == 1){
                $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY, true);
            $this->setScale(0.5);
        }
        $this->setMaxHealth(8);
        $this->setHealth(8);
    }

  public function isBaby() : bool{
    return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY);
  }

public function isSheared() : bool{
  return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SHEARED);
 }

public static function getRandomColor() : int{
		$rand = "";
		$rand .= str_repeat(Wool::WHITE . " ", 20);
		$rand .= str_repeat(Wool::ORANGE . " ", 5);
		$rand .= str_repeat(Wool::MAGENTA . " ", 5);
		$rand .= str_repeat(Wool::LIGHT_BLUE . " ", 5);
		$rand .= str_repeat(Wool::YELLOW . " ", 5);
		$rand .= str_repeat(Wool::GRAY . " ", 10);
		$rand .= str_repeat(Wool::LIGHT_GRAY . " ", 10);
		$rand .= str_repeat(Wool::CYAN . " ", 5);
		$rand .= str_repeat(Wool::PURPLE . " ", 5);
		$rand .= str_repeat(Wool::BLUE . " ", 5);
		$rand .= str_repeat(Wool::BROWN . " ", 5);
		$rand .= str_repeat(Wool::GREEN . " ", 5);
		$rand .= str_repeat(Wool::RED . " ", 5);
		$rand .= str_repeat(Wool::BLACK . " ", 10);
		$arr = explode(" ", $rand);
		return intval($arr[mt_rand(0, count($arr) - 1)]);
	}

public function getColor() : int{
		return (int) $this->namedtag["Color"];
	}

public function setColor(int $color){
		$this->namedtag->Color = new ByteTag("Color", $color);
	}

public function getDrops(){
    if($this->isOnFire()){
   $drops = [
			Item::get(Item::WOOL, $this->getColor(), 1),
     Item::get(Item::COOKED_MUTTON, 0, 1)
		];
   }else{
		$drops = [
			Item::get(Item::WOOL, $this->getColor(), 1),
     Item::get(Item::RAW_MUTTON, 0, 1)
		];
   }
		return $drops;
}

  public function getName(){
    return "Овца";
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
        if($entity->getInventory()->getItemInHand()->getId() === 296){
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

    if($this->motionX === 0 and $this->motionZ === 0){
      $rand = mt_rand(1, 1000);
      if($rand < 10 and $this->getLevel()->getBlock(($pos = $this->subtract(0, 1, 0)))->getId() === 2){
        $pk = new EntityEventPacket();
        $pk->eid = $this->id;
        $pk->event = $rand === 1 ? EntityEventPacket::EAT_GRASS_ANIMATION : EntityEventPacket::AMBIENT_SOUND;
        foreach($this->getLevel()->getPlayers() as $player){
          $player->dataPacket($pk);
        }

        if($rand === 1)
          $this->getLevel()->setBlock($pos, Block::get(Block::DIRT));
      }
    }
    $this->defaultMove();
  }
}
