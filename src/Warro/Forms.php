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

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Warro\forms\SimpleForm;

class Forms{

	public function freeForAll(User|Player $player) : void{
		$form = new SimpleForm(function(User|Player $player, $data = null) : void{
			if(!is_null($data)){
				if($data === -1){
					$player->sendMessage(TextFormat::RED . 'This Arena is unavailable at this moment.');
					return;
				}
				Base::getInstance()->utils->teleport($player, $data, true);
			}
		});

		$players = 0;
		if(Server::getInstance()->getWorldManager()->isWorldLoaded(Variables::NODEBUFF_FFA_ARENA)){
			$players += count(Server::getInstance()->getWorldManager()->getWorldByName(Variables::NODEBUFF_FFA_ARENA)->getPlayers());
		}

		$exec = -1;
		if(Server::getInstance()->getWorldManager()->isWorldLoaded(Variables::NODEBUFF_FFA_ARENA)){
			$exec = 1;
		}

		$form->setTitle(TextFormat::DARK_GREEN . 'Arenas');
		$form->addButton('NoDebuff' . TextFormat::EOL . TextFormat::RESET . TextFormat::DARK_GREEN . $players . ' players', -1, '', $exec);
		$player->sendForm($form);
	}
}