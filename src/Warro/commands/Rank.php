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

namespace Warro\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Warro\Base;

class Rank extends Command{

	public function __construct(private Base $plugin){
		parent::__construct('rank', TextFormat::DARK_GREEN . 'Handle Ranks' . TextFormat::RESET . TextFormat::AQUA . ' [Warro#7777 - discord.gg/vasar]');
		$this->setPermission('gb.command.rank');
		$this->setPermissionMessage(TextFormat::RED . 'Insufficient access.');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender->hasPermission('gb.command.rank') or !Server::getInstance()->isOp($sender->getName())){
			$sender->sendMessage($this->getPermissionMessage());
			return;
		}
		if(!isset($args[0])){
			$sender->sendMessage(TextFormat::RED . 'Argument[0] {Player} required.');
			return;
		}
		if(!isset($args[1])){
			$sender->sendMessage(TextFormat::RED . 'Argument[1] {Rank} required.');
			return;
		}
		$target = Server::getInstance()->getPlayerByPrefix($args[0]);
		if(is_null($target)){
			$sender->sendMessage(TextFormat::RED . 'The Player ' . $args[0] . ' wasn\'t found.');
			return;
		}elseif($target instanceof Player){
			$session = $this->plugin->sessionManager->getSession($target);
			$newRank = $this->plugin->rankManager->getRankFromString($args[1]);
			if(is_null($newRank) or !$this->plugin->rankManager->doesRankExist($newRank)){
				$sender->sendMessage(TextFormat::RED . 'Argument[1] {Rank} provided is invalid.');
				return;
			}
			$session->setRank($newRank);
			$sender->sendMessage(TextFormat::GREEN . 'Successfully set ' . $target->getDisplayName() . '\'s Rank to ' . $session->getRank(true) . '.');
		}
	}
}