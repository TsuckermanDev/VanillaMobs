<?php

namespace VanillaMobs\entity;

use pocketmine\entity\Creature;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\{CompoundTag, ListTag, DoubleTag, FloatTag};

abstract class BaseEntity extends Creature{

 public function setKnockBack($value, $entity){
                $x = $this->x - $entity->x;
                $z = $this->z - $entity->z;
   		$f = sqrt($x * $x + $z * $z);
		if($f <= 0){
			return;
		}
                    $f = 1 / $f;
                    $motion = new Vector3($this->motionX, $this->motionY, $this->motionZ);
		    $motion->x /= 2;
		    $motion->y /= 2;
		    $motion->z /= 2;
		    $motion->x += $x * $f * $value;
		    $motion->y += $value;
		    $motion->z += $z * $f * $value;

			if($motion->y > $value){
				$motion->y = $value;
			}
			$this->setMotion($motion);
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
