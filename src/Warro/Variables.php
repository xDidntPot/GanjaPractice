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

interface Variables
{

	public const NAME = 'Ganja';
	public const DISCORD = 'discord.gg/vasar';
	public const SPAWN = 'world'; // This is your Spawn World, players will originate here
	public const NODEBUFF_FFA_ARENA = 'your ffa world name'; // This is your FFA World, players will teleport here to PvP

	public const COMBAT_TAG = 20;
	public const COMBAT_TAG_KILL = 5;
	public const PEARL_COOLDOWN = 200;
	public const RESPAWN_TIMER = 3; // When you die you'll become a spectator for 3 seconds
	public const AGRO_TICKS = 20; // When your Ender Pearl collides with another player you're given reduced KnockBack for 1 second

	public const TELEPORT_LOBBY = 0;
	public const TELEPORT_NODEBUFF = 1;

	public const KIT_LOBBY = 0;
	public const KIT_NODEBUFF = 1;

}
