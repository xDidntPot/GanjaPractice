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
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Kill extends Command{

	public function __construct(){
		parent::__construct('kill', TextFormat::DARK_GREEN . 'Commit suicide' . TextFormat::RESET . TextFormat::AQUA . ' [Warro#7777 - discord.gg/vasar]');
		$this->setPermission('gb.command.kill');
		$this->setPermissionMessage(TextFormat::RED . 'Insufficient access.');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!isset($args[0])){
			if($sender instanceof Player){
				$sender->attack(new EntityDamageEvent($sender, EntityDamageEvent::CAUSE_SUICIDE, 1000));
			}
		}else{
			if(!Server::getInstance()->isOp($sender->getName())){
				$sender->sendMessage($this->getPermissionMessage());
				return;
			}
			$target = Server::getInstance()->getPlayerByPrefix($args[0]);
			if(is_null($target)){
				$sender->sendMessage(TextFormat::RED . 'The Player ' . $target . ' wasn\'t found.');
				return;
			}elseif($target instanceof Player){
				$target->attack(new EntityDamageEvent($target, EntityDamageEvent::CAUSE_SUICIDE, 1000));
			}
		}
	}
}