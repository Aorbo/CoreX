<?php
/*
*
*  _____                 _            _             
* |_   _|               | |          | |            
*   | |  _ __ ___  _ __ | | __ _  ___| |_ ___  _ __ 
*   | | | '_ ` _ \| '_ \| |/ _` |/ __| __/ _ \| '__|
*  _| |_| | | | | | |_) | | (_| | (__| || (_) | |   
* |_____|_| |_| |_| .__/|_|\__,_|\___|\__\___/|_|   
*                 | |                               
*                 |_|                               
*
* Implactor (1.4.x | 1.5.x)
* A plugin with some features for Minecraft: Bedrock!
* --- = ---
*
* Team: ImpladeDeveloped
* 2018 (c) Zadezter
*
*/
declare(strict_types=1);
namespace Implactor\npc;

use Implactor\MainIR;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class DeathHumanClearEntityTask extends Task {
	
	/** @var Entity $entity */
	private $entity;
	/** @var Player $player */
	private $player;
	
	public function __construct(MainIR $plugin, Entity $entity, Player $player){
		$this->entity = $entity;
		$this->player = $player;
		parent::__construct($plugin);
	}
	
	public function onRun(int $currentTick) : void{
		if($this->entity instanceof DeathHumanEntityTask){
			if($this->entity->getNameTag() === "§7[§cDead§7]§r]" .$this->player->getName(). "") $this->entity->close();
		}
	}
}
