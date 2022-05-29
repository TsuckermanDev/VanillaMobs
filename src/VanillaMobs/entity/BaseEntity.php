<?php

namespace VanillaMobs\entity;

use pocketmine\entity\{Entity, Attribute, Creature};
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\{CompoundTag, ListTag, DoubleTag, FloatTag};
use pocketmine\block\Water;

abstract class BaseEntity extends Creature{
  /*default knockback not stable*/
	public function mobKnockBack(Entity $attacker, $damage, $x, $z, $base = 0.4){
		$f = sqrt($x * $x + $z * $z);
		if($f <= 0){
			return;
		}
		if(mt_rand() / mt_getrandmax() > $this->getAttributeMap()->getAttribute(Attribute::KNOCKBACK_RESISTANCE)->getValue()){
			$f = 1 / $f;

			$motion = new Vector3($this->motionX, $this->motionY, $this->motionZ);

			$motion->x += $x * $f * $base * 2;
			$motion->y = $base / 2.5;
			$motion->z += $z * $f * $base * 2;

			if($motion->y > $base){
				$motion->y = $base / 2.5;
			}

			$this->setMotion($motion);
		}
	}
 /* default insideofwater is not compatible */
	public function insideOfWater() : bool{
    if($this->height > 1 || $this->isBaby()) return $this->level->getBlockAt((int) floor($this->x), (int) floor($this->y + 0.85), (int) floor($this->z)) instanceof Water;
		$block = $this->level->getBlockAt((int) floor($this->x), (int) floor($this->y + 0.7), (int) floor($this->z));

		return $block instanceof Water;
	}

public function nbtShoot(){
            return new CompoundTag("", [
                "Pos" => new ListTag("Pos", [
                    new DoubleTag("", $this->x + (-sin($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI) * 0.5)),
                    new DoubleTag("", $this->y + 1.62),
                    new DoubleTag("", $this->z +(cos($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI) * 0.5))
                ]),
                "Motion" => new ListTag("Motion", [
                    new DoubleTag("", -sin($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI) * 1.2),
                    new DoubleTag("", -sin($this->pitch / 180 * M_PI) * 1.2),
                    new DoubleTag("", cos($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI) * 1.2)
                ]),
                "Rotation" => new ListTag("Rotation", [
                    new FloatTag("", $this->yaw),
                    new FloatTag("", $this->pitch)
                ]),
            ]);
  }
}
