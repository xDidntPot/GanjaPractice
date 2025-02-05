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

class Ping extends Command{

	public function __construct(){
		parent::__construct('ping', TextFormat::DARK_GREEN . 'View your latency' . TextFormat::RESET . TextFormat::AQUA . ' [Warro#7777 - discord.gg/vasar]');
		$this->setPermission('gb.command.ping');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!isset($args[0])){
			if($sender instanceof Player){
				$sender->sendMessage(TextFormat::GREEN . 'Your Ping: ' . TextFormat::WHITE . $sender->getNetworkSession()->getPing());
			}
		}else{
			$target = Server::getInstance()->getPlayerByPrefix($args[0]);
			if(is_null($target)){
				$sender->sendMessage(TextFormat::RED . 'The Player ' . $target . ' wasn\'t found.');
				return;
			}elseif($target instanceof Player){
				$sender->sendMessage(TextFormat::GREEN . $target->getDisplayName() . '\'s Ping: ' . TextFormat::WHITE . $target->getNetworkSession()->getPing());
			}
		}
	}
}