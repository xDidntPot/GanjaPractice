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

class Rekit extends Command{

	public function __construct(private Base $plugin){
		parent::__construct('rekit', TextFormat::DARK_GREEN . 'Replenish your current FFA Kit' . TextFormat::RESET . TextFormat::AQUA . ' [Warro#7777 - discord.gg/vasar]');
		$this->setPermission('gb.command.rekit');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player){
			if(!Server::getInstance()->isOp($sender->getName())){
				if($this->plugin->utils->isTagged($sender)){
					$sender->sendMessage(TextFormat::RED . 'Please wait until you\'re out of Combat.');
					return;
				}
			}
			if($this->plugin->utils->isInFfa($sender)){
				$this->plugin->utils->kit($sender, 0, true);
			}
		}
	}
}