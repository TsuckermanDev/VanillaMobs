<?php

namespace VanillaMobs;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
use pocketmine\scheduler\PluginTask;
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

class Main extends PluginBase implements Listener {

    private static $classes = array();

    public function onLoad() : void {
        $classes = array(
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


        foreach ($classes as $class) {
            Entity::registerEntity($class);
        }
        $item = Item::get(Item::SPAWN_EGG, $class::NETWORK_ID);
        if (!Item::isCreativeItem($item)) {
            Item::addCreativeItem($item);
        }
    }
    
    public function shearSheep(DataPacketReceiveEvent $event) {
        $packet = $event->getPacket();
        $player = $event->getPlayer();

        if ($packet instanceof InteractPacket) {
            if ($packet->action === InteractPacket::ACTION_RIGHT_CLICK) {
                if ($player->getItemInHand()->getId() != 359) return false;
                $entity = $player->level->getEntity($packet->target);
                if ($entity instanceof Sheep) {
                    if ($entity->isSheared() || $entity->isBaby()) return false;
                    $player->getLevel()->dropItem($entity, Item::get(Item::WOOL, $entity->getColor(), mt_rand(1, 3)));
                    $entity->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SHEARED, true);
                    $player->setDataProperty(Entity::DATA_INTERACTIVE_TAG, Entity::DATA_TYPE_STRING, "");
                }
            }
        }
    }

    public function onEnable() : void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }



}
