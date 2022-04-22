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

namespace Warro\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use Warro\Base;
use Warro\User;
use function count;

class CombatTask extends Task
{

	public function __construct(private Base $plugin)
	{
	}

	public function onRun(): void
	{
		if (count($this->plugin->utils->taggedPlayer) > 0) {
			foreach ($this->plugin->utils->taggedPlayer as $name => $time) {
				$player = Server::getInstance()->getPlayerExact($name);
				if ($player instanceof User) {
					if ($time <= 0) {
						if ($this->plugin->utils->isTagged($player)) {
							$this->plugin->utils->setTagged($player, false, true);
						}
						return;
					}
					$this->plugin->utils->taggedPlayer[$name]--;
				}
			}
		}
	}
}