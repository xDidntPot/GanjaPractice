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

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\Server;

class WorldListener implements Listener{

	/**
	 * @priority HIGHEST
	 */
	public function onPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		if(!Server::getInstance()->isOp($player->getName())){
			$event->cancel();
			return;
		}
		if(!$player->isCreative()){
			$event->cancel();
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if(!Server::getInstance()->isOp($player->getName())){
			$event->cancel();
			return;
		}
		if(!$player->isCreative()){
			$event->cancel();
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onBucketFill(PlayerBucketFillEvent $event) : void{
		$player = $event->getPlayer();
		if(!Server::getInstance()->isOp($player->getName())){
			$event->cancel();
			return;
		}
		if(!$player->isCreative()){
			$event->cancel();
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onBucketEmpty(PlayerBucketEmptyEvent $event) : void{
		$player = $event->getPlayer();
		if(!Server::getInstance()->isOp($player->getName())){
			$event->cancel();
			return;
		}
		if(!$player->isCreative()){
			$event->cancel();
		}
	}

	/**
	 * @priority HIGHEST
	 */
	public function onCraft(CraftItemEvent $event){
		$event->cancel();
	}

	/**
	 * @priority HIGHEST
	 */
	public function onLeaveDecay(LeavesDecayEvent $event){
		$event->cancel();
	}

	/**
	 * @priority HIGHEST
	 */
	public function onBurn(BlockBurnEvent $event){
		$event->cancel();
	}

	/**
	 * @priority HIGHEST
	 */
	public function onUpdate(BlockUpdateEvent $event){
		$event->cancel();
	}
}