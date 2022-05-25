<?php

namespace VanillaMobs;

use pocketmine\block\BlockIds;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use VanillaMobs\entity\projectile\ {
    LargeFireball,
    LittleFireball
};
use VanillaMobs\entity\animal\walking\ {
    Sheep,
    Cow,
    Chicken,
    Pig
};
use VanillaMobs\entity\monster\walking\ {
    Zombie,
    Skeleton,
    Husk,
    Enderman
};

class Loader extends PluginBase implements Listener
{

    private static $classes = array(
        Pig::class,
        Sheep::class,
        Cow::class,
        Chicken::class,
        Zombie::class,
        Skeleton::class,
        Husk::class,
        Enderman::class,
        LargeFireball::class,
        LittleFireball::class
    );

	/**
	 * @return void
	 */
	public function onLoad(): void
    {
        for ($i = 0; $i < sizeof(static::$classes); $i++) {
            Entity::registerEntity(static::$classes[$i]);
            $item = Item::get(ItemIds::SPAWN_EGG, static::$classes[$i]::NETWORK_ID);
            if (!Item::isCreativeItem($item)) {
                Item::addCreativeItem($item);
            }
        }
		$this->getLogger()->notice('Загружено '.sizeof(static::$classes).' мобов');
    }

	/**
	 * @param DataPacketReceiveEvent $event
	 * @return bool
	 */
	public function shearSheep(DataPacketReceiveEvent $event) : bool
    {
        $packet = $event->getPacket();
        $player = $event->getPlayer();

        if ($packet instanceof InteractPacket) {
            if ($packet->action === InteractPacket::ACTION_RIGHT_CLICK) {
                if ($player->getItemInHand()->getId() != 359) return false;
                $entity = $player->level->getEntity($packet->target);
                if ($entity instanceof Sheep) {
                    if ($entity->isSheared() || $entity->isBaby()) return false;
                    $player->getLevel()->dropItem($entity, Item::get(BlockIds::WOOL, $entity->getColor(), mt_rand(1, 3)));
                    $entity->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SHEARED);
                    $player->setDataProperty(Entity::DATA_INTERACTIVE_TAG, Entity::DATA_TYPE_STRING, "");
                }
            }
        }
        return false;
    }

	/**
	 * @return void
	 */
	public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
}
