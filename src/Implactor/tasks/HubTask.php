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
namespace Implactor\tasks;

use pocketmine\{
	Server, Player
};
use pocketmine\scheduler\Task;
use pocketmine\{
	Level, Position
};
use pocketmine\level\sound\{
	PopSound, FizzSound
};
use pocketmine\utils\TextFormat as IR;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;

use Implactor\MainIR;
use Implactor\particles\TeleportHubParticle;

class HubTask extends Task {
	
	/** @var Plugin */
	private $plugin;
	/** @var Seconds */
	public $seconds; 
	/** @var Player */
	private $player;
	
	public function __construct(MainIR $plugin, Player $player) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
		$this->player = $player;
		$this->seconds = 5;
		}
		
		public function getServer(){
			return $this->plugin;
		}
		
		public function onRun($ticks): void{
			if($this->player instanceof Player){
			if($this->seconds === 10){
				$this->player->sendTip("§eReturning in §a10§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 9){
				$this->player->sendTip("§eReturning in §a9§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 8){
				$this->player->sendTip("§eReturning in §a8§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 7){
				$this->player->sendTip("§eReturning in §a7§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 6){
				$this->player->sendTip("§eReturning in §a6§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 5){
				$this->player->sendTip("§eReturning in §c5§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 4){
				$this->player->sendTip("§eReturning in §c4§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 3){
				$this->player->sendTip("§eReturning in §c3§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 2){
				$this->player->sendTip("§eReturning in §c2§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 1){
				$this->player->sendTip("§eReturning in §c1§e...");
				$this->player->getLevel()->addSound(new PopSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
			}
			if($this->seconds === 0){
				$this->player->sendTip("§eReturned back to hub!");
				$this->player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
				$this->player->addTitle("§7§l[§eHUB§7]§r", "§a-- Yay! --§f...");
			    $this->player->sendMessage(IR::GRAY. "-------" .IR::WHITE. "\n Returning to hub..." .IR::GRAY. "\n-------");
				$this->player->getLevel()->addSound(new FizzSound($this->player));
				$this->plugin->getServer()->getScheduler()->schedulerDelayedTask(new TeleportHubParticle($this->player), 20);
				$this->plugin->getServer()->removeTask($this->plugin->getTaskId());
			  }
			}
			  $this->seconds--;
		}
        
		public function getPlugin(){
			return $this->plugin;
		}
   }
			
     
