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
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use Warro\Base;
use Warro\managers\RankManager;
use Warro\User;

class Nick extends Command
{

	public function __construct(private Base $plugin)
	{
		parent::__construct('nick', TextFormat::DARK_GREEN . 'Modify your username' . TextFormat::RESET . TextFormat::AQUA . ' [Warro#7777 - discord.gg/vasar]');
		$this->setPermissionMessage(TextFormat::RED . 'Insufficient access.');
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!Server::getInstance()->isOp($sender->getName())) {
			$sender->sendMessage($this->getPermissionMessage());
			return;
		}
		if ($sender instanceof User) {
			$session = $this->plugin->sessionManager->getSession($sender);
			if ($session->getRank() < RankManager::VIP) {
				$sender->sendMessage(TextFormat::RED . 'You don\'t have access to this Command.');
				return;
			}
			if (!isset($args[0])) {
				$sender->sendMessage(TextFormat::RED . 'Argument[0] {Nickname} required.');
				return;
			}
			$nick = str_replace(' ', '', $args[0]);
			foreach (Server::getInstance()->getOnlinePlayers() as $online) {
				if (strtolower($nick) === strtolower($online->getDisplayName())) {
					$sender->sendMessage(TextFormat::RED . 'You can\'t use that as a Nickname.');
					return;
				}
			}
			if (!ctype_alnum($nick)) {
				$sender->sendMessage(TextFormat::RED . 'Your Nickname can only contain letters and numbers.');
				return;
			}
			if (strlen($nick) < 3) {
				$sender->sendMessage(TextFormat::RED . 'Your Nickname can\'t have less than 3 characters.');
				return;
			}
			if (strlen($nick) > 13) {
				$sender->sendMessage(TextFormat::RED . 'Your Nickname can\'t have more than 13 characters.');
				return;
			}
			switch (strtolower($args[0])) {
				case 'off':
				case 'close':
				case 'reset':
				case 'disable':
				case 'remove':
					$sender->setDisplayName($sender->getDisplayName());
					$sender->sendMessage(TextFormat::GREEN . 'Successfully reset your Nickname to ' . $sender->getDisplayName() . '.');
					break;
				default:
					$sender->setDisplayName($args[0]);
					$sender->sendMessage(TextFormat::GREEN . 'Successfully set your Nickname to ' . $sender->getDisplayName() . '.');
					break;
			}
			$sender->setNameTag($this->plugin->utils->getTagFormat($sender));
			$sender->respawnToAll();
		}
	}
}