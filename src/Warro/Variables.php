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

interface Variables{

	public const string NAME = 'Ganja';
	public const string DISCORD = 'discord.gg/vasar';
	public const string SPAWN = 'world'; // This is your Spawn World, players will originate here
	public const string NODEBUFF_FFA_ARENA = 'nodebuff'; // This is your FFA World, players will teleport here to PvP

	public const int COMBAT_TAG = 20;
	public const int COMBAT_TAG_KILL = 5;
	public const int PEARL_COOLDOWN = 200;
	public const int RESPAWN_TIMER = 3; // When you die you'll become a spectator for 3 seconds
	public const int AGRO_TICKS = 20; // When your Ender Pearl collides with another player you're given reduced KnockBack for 1 second

	public const int TELEPORT_LOBBY = 0;
	public const int TELEPORT_NODEBUFF = 1;

	public const int KIT_LOBBY = 0;
	public const int KIT_NODEBUFF = 1;

}
