<?php

/*
 *
 * Developed by Warro#7777
 * Join Ganja: ganja.bet:19132
 * My Discord: https://discord.gg/vasar
 * Repository: https://github.com/Wqrro/Ganja
 *
 */

declare(strict_types=1);

namespace Warro;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\EffectIds;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\entity\animation\DeathAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\PotionType;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\Network;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Warro\entities\DeadEntity;
use Warro\items\VasarItemEnderPearl;
use Warro\items\VasarItemSplashPotion;
use Warro\managers\RankManager;

class Utils{

	public array $taggedPlayer = [];
	public array $pearlPlayer = [];

	public array $chatCooldown = [];

	public array $links = ['.xyz', '.me', '.club', 'www.', '.com', '.net', '.gg', '.cc', '.net', '.co', '.co.uk', '.ddns',
		'.ddns.net', '.cf', '.live', '.ml', '.gov', 'http://', 'https://', ',club', 'www,', ',com', ',cc', ',net', ',gg',
		',co', ',couk', ',ddns', ',ddns.net', ',cf', ',live', ',ml', ',gov', ',xyz', 'http://', 'https://', 'gg/'];

	public ?Location $spawnLocation = null;
	public ?Location $nodebuffLocation = null;

	public function getChatFormat(Player $player, PlayerChatEvent $event) : string{
		$session = Base::getInstance()->sessionManager->getSession($player);

		return match ($session->getRank()) {
			RankManager::VIP => TextFormat::DARK_PURPLE . '*' . TextFormat::LIGHT_PURPLE . $player->getDisplayName() . TextFormat::DARK_GRAY . ': ' . TextFormat::RESET . TextFormat::WHITE . $event->getMessage(),
			RankManager::OWNER => TextFormat::ITALIC . TextFormat::DARK_AQUA . '*' . TextFormat::AQUA . $player->getDisplayName() . TextFormat::DARK_GRAY . ': ' . TextFormat::RESET . TextFormat::WHITE . $event->getMessage(),

			default => TextFormat::RED . $player->getDisplayName() . ': ' . TextFormat::WHITE . $event->getMessage(),
		};
	}

	public function getTagFormat(Player $player) : string{
		$session = Base::getInstance()->sessionManager->getSession($player);

		return match ($session->getRank()) {
			RankManager::VIP => TextFormat::DARK_PURPLE . '*' . TextFormat::LIGHT_PURPLE . $player->getDisplayName(),
			RankManager::OWNER => TextFormat::ITALIC . TextFormat::DARK_AQUA . '*' . TextFormat::AQUA . $player->getDisplayName(),

			default => TextFormat::RED . $player->getDisplayName(),
		};
	}

	public function teleport(Player $player, int $where, bool $doKit = false) : void{
		$session = Base::getInstance()->sessionManager->getSession($player);

		switch($where){
			default:
				$player->teleport($this->spawnLocation);
				break;
			case Variables::TELEPORT_NODEBUFF:
				$player->teleport($this->nodebuffLocation);
				break;
		}

		$player->setNameTag($this->getTagFormat($player));

		$this->setTagged($player, false, false, true, -1);
		$this->setPearlCooldown($player, false);

		$session->maxDistanceKnockBack = 3; // Different from what's seen on Ganja!
		$session->heightLimiterKnockBack = 0.026;
		$session->attackCooldown = 10;

		$session->hKnockBack = match ($where) {
			default => 0.0,
			Variables::TELEPORT_NODEBUFF => 0.3905, // Different from what's seen on Ganja!
		};
		$session->vKnockBack = match ($where) {
			default => 0.0,
			Variables::TELEPORT_NODEBUFF => 0.3975, // Different from what's seen on Ganja!
		};

		$kit = match ($where) {
			default => Variables::KIT_LOBBY,
			Variables::TELEPORT_NODEBUFF => Variables::KIT_NODEBUFF,
		};

		if($doKit){
			$player->noDamageTicks = 20;
			$this->kit($player, $kit);
		}
	}

	public function kit(Player $player, int $kit, bool $guess = false) : void{
		if(!$player instanceof User){
			return;
		}

		$session = Base::getInstance()->sessionManager->getSession($player);

		if(!$session->canTakeDamage()){
			return;
		}

		if($guess){
			$kit = match ($player->getWorld()?->getFolderName()) {
				Variables::NODEBUFF_FFA_ARENA => Variables::KIT_NODEBUFF,
				default => 0,
			};
		}

		$player->setInvisible(false);
		$player->setNoClientPredictions(false);
		$session->setLastDamagePosition();
		$player->setHealth(20);
		$player->getEffects()->clear();
		$player->extinguish();
		$player->setAbsorption(0.0);
		$player->getInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->setFlying(false);
		$player->setAllowFlight(false);
		$player->setGamemode(GameMode::ADVENTURE());
		$player->getXpManager()->setXpAndProgress(0, 0.0);
		$player->getInventory()->setHeldItemIndex(0);

		switch($kit){
			case Variables::KIT_LOBBY:
				$ffa = VanillaItems::DIAMOND_SWORD();
				$ffa->setCustomName(TextFormat::RESET . TextFormat::DARK_GREEN . 'Arenas');

				$player->getInventory()->setItem(0, $ffa);
				break;
			case Variables::KIT_NODEBUFF:
				$helmet = VanillaItems::DIAMOND_HELMET();
				$helmet->setCustomName(TextFormat::RESET . TextFormat::DARK_GREEN . Variables::NAME);
				$helmet->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING), 10));
				$player->getArmorInventory()->setHelmet($helmet->setUnbreakable(true));
				$chestplate = VanillaItems::DIAMOND_CHESTPLATE();
				$chestplate->setCustomName(TextFormat::RESET . TextFormat::DARK_GREEN . Variables::NAME);
				$chestplate->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING), 10));
				$player->getArmorInventory()->setChestplate($chestplate->setUnbreakable(true));
				$leggings = VanillaItems::DIAMOND_LEGGINGS();
				$leggings->setCustomName(TextFormat::RESET . TextFormat::DARK_GREEN . Variables::NAME);
				$leggings->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING), 10));
				$player->getArmorInventory()->setLeggings($leggings->setUnbreakable(true));
				$boots = VanillaItems::DIAMOND_BOOTS();
				$boots->setCustomName(TextFormat::RESET . TextFormat::DARK_GREEN . Variables::NAME);
				$boots->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING), 10));
				$player->getArmorInventory()->setBoots($boots->setUnbreakable(true));
				$sword = VanillaItems::DIAMOND_SWORD();
				$sword->setCustomName(TextFormat::RESET . TextFormat::DARK_GREEN . Variables::NAME);
				$sword->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::UNBREAKING), 10));

				$pearls = new VasarItemEnderPearl(new ItemIdentifier(ItemTypeIds::ENDER_PEARL));
				$pearls->setCount(16);
				$pearls->setCustomName(TextFormat::RESET . TextFormat::DARK_GREEN . Variables::NAME);

				$type = PotionType::STRONG_HEALING();
				$pots = new VasarItemSplashPotion(new ItemIdentifier(ItemTypeIds::SPLASH_POTION), $type->getDisplayName() . ' Splash Potion', $type);
				$pots->setCount(36);
				$pots->setCustomName(TextFormat::RESET . TextFormat::DARK_GREEN . Variables::NAME);

				$player->getInventory()->setItem(0, $sword->setUnbreakable(true));
				$player->getInventory()->setItem(1, $pearls);
				$player->getInventory()->addItem($pots);

				$player->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId(EffectIds::SPEED), 9999 * 9999, 0, false));
				break;
		}
	}

	public function doDamageCheck(Player $player, EntityDamageEvent $event) : void{
		if(!$player instanceof User){
			return;
		}

		$session = Base::getInstance()->sessionManager->getSession($player);

		$damager = $session->getDamager();
		if(!$event->isCancelled() and $player->isSurvival() and $player->isAlive() and $session->canTakeDamage()){
			$final = $event->getFinalDamage();
			$health = $player->getHealth();
			if($final >= $health){
				$event->cancel();
				$damager = is_null($damager) ? null : Server::getInstance()->getPlayerExact($damager);
				$this->onDeath($player, $damager);
			}
		}
	}

	public function onDeath(Player $player, Player|null $killer = null, bool $animation = true, bool $actuallyDied = false) : void{

		if($killer instanceof User and $player->getName() === $killer->getName()){
			return;
		}

		$session = Base::getInstance()->sessionManager->getSession($player);

		$session->addDeath();
		$session->resetKillstreak();
		$session->resetAgroTimer();

		if($killer instanceof User and $this->isInFfa($killer)){
			$sessionKiller = Base::getInstance()->sessionManager->getSession($killer);

			$sessionKiller->addKill();
			$sessionKiller->addToKillstreak();
			if($this->isInNoDebuffFfa($killer)){
				$killerInfo = 0;
				$playerInfo = 0;
				foreach($killer->getInventory()->getContents() as $contents){
					if($contents->getTypeId() === ItemTypeIds::SPLASH_POTION){
						$killerInfo++;
					}
				}
				foreach($player->getInventory()->getContents() as $contents){
					if($contents->getTypeId() === ItemTypeIds::SPLASH_POTION){
						$playerInfo++;
					}
				}
				$message = TextFormat::GREEN . $killer->getDisplayName() . TextFormat::DARK_GREEN . '[' . $killerInfo . ']' . TextFormat::DARK_GRAY . ' - ' . TextFormat::RED . $player->getDisplayName() . TextFormat::DARK_RED . '[' . $playerInfo . ']';
			}else{
				$message = TextFormat::GREEN . $killer->getDisplayName() . TextFormat::DARK_GRAY . ' - ' . TextFormat::RED . $player->getDisplayName();
			}

			Server::getInstance()->broadcastMessage($message);

			$this->setTagged($killer, false, true, true, -1);

			$this->kit($killer, Variables::KIT_NODEBUFF);
		}

		$this->setTagged($player, false, true, true, -1);
		$this->setPearlCooldown($player, false);

		if($animation){
			$human = new DeadEntity($player->getLocation(), $player->getSkin());

			$human->setScale($player->getScale());
			$human->setSkin($player->getSkin());
			$human->setNameTagAlwaysVisible($player->isNameTagAlwaysVisible());
			$human->setNameTagVisible($player->isNameTagVisible());
			$human->setNameTag($player->getNameTag());
			$human->getInventory()->setContents($player->getInventory()->getContents());
			$human->getInventory()->setHeldItemIndex($player->getInventory()->getHeldItemIndex());
			$human->getInventory()->setItemInHand($player->getInventory()->getItemInHand());
			$human->getArmorInventory()->setContents($player->getArmorInventory()->getContents());

			$human->spawnToAll();
			$player->setInvisible(true);

			$human->broadcastAnimation(new DeathAnimation($human), $player->getViewers());
			$human->broadcastAnimation(new DeathAnimation($human), [$player]);

			if($player instanceof User and $killer instanceof User){
				$human->shove($human->getPosition()->getX() - $killer->getPosition()->getX(), $human->getPosition()->getZ() - $killer->getPosition()->getZ(), 0.5);
				$this->doLightning($human, $player);
			}

			$player->setHealth(20);
			$player->getEffects()->clear();
			$player->extinguish();
			$player->setAbsorption(0.0);
			$player->getInventory()->clearAll();
			$player->getCursorInventory()->clearAll();
			$player->getArmorInventory()->clearAll();
			$player->setSneaking(false);
			$player->setSprinting(false);
			$player->setFlying(true);
			$player->setAllowFlight(true);
			$player->setGamemode(GameMode::SPECTATOR());
			$player->getXpManager()->setXpAndProgress(0, 0.0);
			$session->setTakeDamage(false);
		}

		$time = $actuallyDied ? 0 : Variables::RESPAWN_TIMER;
		$session->startRespawnTimer($time);
	}

	public function doLightning(?Human $human, Player $player){
		if(is_null($human)){
			$human = $player;
		}

		$location = $human->getLocation();

		$lightning = new AddActorPacket();

		$lightning->actorUniqueId = Entity::nextRuntimeId();
		$lightning->actorRuntimeId = $lightning->actorUniqueId;
		$lightning->type = 'minecraft:lightning_bolt';
		$lightning->position = $location->asVector3();
		$lightning->motion = null;
		$lightning->pitch = $location->getPitch();
		$lightning->yaw = $location->getYaw();
		$lightning->headYaw = 0.0;
		$lightning->attributes = [];
		$lightning->metadata = [];
		$lightning->links = [];

		$thunder = new PlaySoundPacket();
		$thunder->soundName = 'ambient.weather.thunder';
		$thunder->x = $location->getX();
		$thunder->y = $location->getY();
		$thunder->z = $location->getZ();
		$thunder->volume = 1;
		$thunder->pitch = 1;

		NetworkBroadcastUtils::broadcastPackets($player->getViewers(), [$lightning, $thunder]);
	}

	public function setPearlCooldown(Player $player, bool $value = true, bool $notify = false, int $time = Variables::PEARL_COOLDOWN) : void{
		if(!$player instanceof User){
			return;
		}

		if(!$player->isSurvival()){
			return;
		}

		if($value){
			if(!$this->isInPearlCooldown($player)){
				if($notify){
					$player->sendActionBarMessage(TextFormat::RED . 'Pearl-Cooldown Started');
				}
			}
			$this->pearlPlayer[$player->getName()] = $time;
		}else{
			if($this->isInPearlCooldown($player)){
				unset($this->pearlPlayer[$player->getName()]);
				if($notify){
					$player->sendActionBarMessage(TextFormat::GREEN . 'Pearl-Cooldown Expired');
				}
			}
		}
	}

	public function isInPearlCooldown(Player $player) : bool{
		return isset($this->pearlPlayer[$player->getName()]);
	}

	public function setTagged(Player $player, bool $value = true, bool $notify = false, bool $clearDamager = true, int $time = Variables::COMBAT_TAG) : void{
		if(!$player instanceof User){
			return;
		}

		if(!$player->isSurvival()){
			return;
		}

		$session = Base::getInstance()->sessionManager->getSession($player);

		if($value){
			if(!$this->isTagged($player)){
				if($notify){
					$player->sendActionBarMessage(TextFormat::RED . 'Combat-Tag Started');
				}
			}else{
				$originalTime = $this->taggedPlayer[$player->getName()];
				if($originalTime >= Variables::COMBAT_TAG_KILL and $time === Variables::COMBAT_TAG_KILL){
					$player->sendActionBarMessage(TextFormat::GOLD . 'Combat-Tag Reduced');
				}
			}
			$this->taggedPlayer[$player->getName()] = $time;
		}else{
			if($this->isTagged($player)){
				unset($this->taggedPlayer[$player->getName()]);
				if($notify){
					$player->sendActionBarMessage(TextFormat::GREEN . ($time === -1 ? 'Combat-Tag Removed' : 'Combat-Tag Expired'));
				}
				if($clearDamager){
					$session->setDamager();
				}
			}
		}
	}

	public function isTagged(Player $player) : bool{
		return isset($this->taggedPlayer[$player->getName()]);
	}

	public function isInSpawn(Player $player) : bool{
		return $player->getWorld()->getFolderName() === Variables::SPAWN;
	}

	public function isInNoDebuffFfa(Player $player) : bool{
		return $player->getWorld()->getFolderName() === Variables::NODEBUFF_FFA_ARENA;
	}

	public function isInFfa(Player $player) : bool{
		return $this->isInNoDebuffFfa($player);
	}
}