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

namespace Warro\listeners;

use pocketmine\block\Cactus;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\{EntityDamageByBlockEvent,
	EntityDamageByChildEntityEvent,
	EntityDamageByEntityEvent,
	EntityDamageEvent};
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\chat\LegacyRawChatFormatter;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Warro\Base;
use Warro\Session;
use Warro\User;
use Warro\Variables;

class PlayerListener implements Listener{

	public function __construct(private Base $plugin){
	}

	/**
	 * @priority HIGHEST
	 */
	public function onCreation(PlayerCreationEvent $event) : void{
		$event->setPlayerClass(User::class);
	}

	/**
	 * @priority HIGHEST
	 */
	public function onLogin(PlayerLoginEvent $event) : void{
		$player = $event->getPlayer();

		if(!$player instanceof User){
			return;
		}

		$this->plugin->sessionManager->createSession($player);
	}

	/**
	 * @priority HIGHEST
	 */
	public function onJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$event->setJoinMessage(TextFormat::ITALIC . TextFormat::DARK_GREEN . ' + ' . TextFormat::GRAY . $player->getDisplayName());

		if(!$player instanceof User){
			return;
		}

		$session = $this->plugin->sessionManager->getSession($player);
		if(!$session instanceof Session){
			$player->kick(TextFormat::RED . 'Error creating session, please try reconnecting.');
			return;
		}

		$session->onJoin();
	}

	/**
	 * @priority HIGHEST
	 */
	public function onQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		$event->setQuitMessage(TextFormat::ITALIC . TextFormat::DARK_RED . ' - ' . TextFormat::GRAY . $player->getDisplayName());

		if(!$player instanceof User){
			return;
		}

		$session = Base::getInstance()->sessionManager->getSession($player);

		if($session === null){
			return;
		}

		$session->onQuit($event);
	}

	/**
	 * @priority HIGHEST
	 */
	public function onChat(PlayerChatEvent $event) : void{
		$player = $event->getPlayer();

		if(!$player instanceof User){
			return;
		}

		$message = str_replace(' ', '', strtolower($event->getMessage()));
		$format = $this->plugin->utils->getChatFormat($player, $event);
		$event->setFormatter(new LegacyRawChatFormatter($format));

		$cooldown = Server::getInstance()->isOp($player->getName()) ? 0 : 3;
		if(isset(Base::getInstance()->utils->chatCooldown[$player->getName()]) and time() - Base::getInstance()->utils->chatCooldown[$player->getName()] < $cooldown){
			$player->sendMessage(TextFormat::RED . 'Please wait before chatting again.');
			$event->cancel();
			return;
		}

		if(!Server::getInstance()->isOp($player->getName())){
			foreach($this->plugin->utils->links as $links){
				if(str_contains($message, $links)){
					$player->sendMessage(TextFormat::RED . 'Please refrain from advertising.');
					$event->cancel();
					break;
				}
			}
		}

		Base::getInstance()->utils->chatCooldown[$player->getName()] = time();
	}

	/**
	 * @priority HIGHEST
	 */
	public function onUseItem(PlayerItemUseEvent $event) : void{
		$player = $event->getPlayer();
		$item = $event->getItem();

		if(!$player instanceof User){
			return;
		}

		$name = $item->getCustomName();

		if($name === TextFormat::RESET . TextFormat::DARK_GREEN . 'Arenas'){
			Base::getInstance()->forms->freeForAll($player);
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onMove(PlayerMoveEvent $event) : void{
		$player = $event->getPlayer();

		if(!$player instanceof User){
			return;
		}

		if($this->plugin->utils->isInSpawn($player)){
			if($player->getLocation()->getY() <= 0){
				$this->plugin->utils->teleport($player, Variables::TELEPORT_LOBBY);
			}
		}else{
			if($player->getLocation()->getY() <= 0){
				$player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_SUICIDE, 1000));
			}
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onDeath(PlayerDeathEvent $event) : void{
		$player = $event->getPlayer();
		$event->setDeathMessage('');
		$event->setDrops([]);
		$event->setXpDropAmount(0);

		if(!$player instanceof User){
			return;
		}

		$session = Base::getInstance()->sessionManager->getSession($player);

		if($session === null){
			return;
		}

		if($session->hasDamager()){
			$damager = Server::getInstance()->getPlayerExact($session->getDamager());
			if($damager instanceof User){
				Base::getInstance()->utils->onDeath($player, $damager);
			}
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onRespawn(PlayerRespawnEvent $event) : void{
		$player = $event->getPlayer();

		if(!$player instanceof User){
			return;
		}

		$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player) : void{
			$this->plugin->utils->teleport($player, Variables::TELEPORT_LOBBY, true);
		}), 2);
	}

	/**
	 * @priority HIGHEST
	 */
	public function onEntityDamage(EntityDamageEvent $event) : void{
		$player = $event->getEntity();
		$cause = $event->getCause();

		if(!$player instanceof User){
			return;
		}

		$session = Base::getInstance()->sessionManager->getSession($player);

		if($session === null){
			return;
		}

		if($this->plugin->utils->isInSpawn($player) or $cause === EntityDamageEvent::CAUSE_FALL or !$session->canTakeDamage()){
			$event->cancel();
			return;
		}

		if($event instanceof EntityDamageByEntityEvent and !$event instanceof EntityDamageByChildEntityEvent){
			$damager = $event->getDamager();
			if(!$event->isCancelled()){
				if($damager instanceof User){
					if($this->plugin->cpsManager->doesPlayerExist($damager)){
						if($this->plugin->cpsManager->getCps($damager) >= 20){
							$damager->sendActionBarMessage(TextFormat::RED . 'Your hits are being canceled out, reduce your CPS.');
							$event->cancel();
							return;
						}
					}
				}

				foreach([$player, $damager] as $players){
					$this->plugin->utils->setTagged($players, true, true);
				}

				$sessionDamager = Base::getInstance()->sessionManager->getSession($damager);
				$session->setDamager($damager->getName());
				$session->setLastDamagePosition($damager->getPosition());
				$sessionDamager->setDamager($player->getName());
			}

			Base::getInstance()->utils->doDamageCheck($player, $event);
		}elseif($event instanceof EntityDamageByChildEntityEvent){
			$damager = $event->getChild();
			$owner = $damager->getOwningEntity();
			if($owner instanceof User){
				if($damager instanceof Projectile){
					if($player->getName() === $owner->getName()){
						$event->cancel();
						return;
					}
				}

				$sessionOwner = Base::getInstance()->sessionManager->getSession($owner);

				if(!$event->isCancelled()){
					foreach([$player, $owner] as $players){
						Base::getInstance()->utils->setTagged($players, true, true);
					}
					$session->setDamager($owner->getName());
					$sessionOwner->setDamager($player->getName());
				}
			}

			Base::getInstance()->utils->doDamageCheck($player, $event);
		}elseif($event instanceof EntityDamageByBlockEvent){
			$damager = $event->getDamager();
			if($damager instanceof Cactus){
				$event->cancel();
				return;
			}
		}else{
			Base::getInstance()->utils->doDamageCheck($player, $event);
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onExhaust(PlayerExhaustEvent $event) : void{
		$event->cancel();
	}

	/**
	 * @priority HIGHEST
	 */
	public function onDropItem(PlayerDropItemEvent $event) : void{
		$player = $event->getPlayer();

		if(!$player instanceof User){
			return;
		}

		if(!$player->isCreative(true)){
			$event->cancel();
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onDataReceive(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		if($packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID and $packet instanceof LevelSoundEventPacket){
			$player = $event->getOrigin()->getPlayer();
			if($player instanceof User){
				if($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE){
					$player->broadcastAnimation(new ArmSwingAnimation($player), $player->getViewers());
				}
			}
			if($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE or $packet->sound === LevelSoundEvent::ATTACK_STRONG){
				if($this->plugin->cpsManager->doesPlayerExist($player)){
					$this->plugin->cpsManager->addClick($player);
				}
			}
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onDataSend(DataPacketSendEvent $event) : void{
		$packets = $event->getPackets();
		foreach($packets as $packet){
			if($packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID and $packet instanceof LevelSoundEventPacket){
				if($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE or $packet->sound === LevelSoundEvent::ATTACK_STRONG){
					$event->cancel();
				}
			}
		}
	}
}