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
namespace Implactor\particles;

use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\Plugin;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\Location;
use Implactor\MainIR;

class HubParticle extends Task {
	
	public function __construct(MainIR $plugin){
		$this->plugin = $plugin;
		parent::__construct($plugin);
	}
	
	public function onRun(int $currentTick): void{
		$level = $this->plugin->getServer()->getDefaultLevel();
		$hub = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
		$r = rand(1,300);
		$g = rand(1,300);
		$b = rand(1,300);
		$x = $hub->getX();
		$y = $hub->getY();
		$z = $hub->getZ();
		$center = new Vector3($x, $y, $z);
		$radius = 0.5;
		$count = 100;
		$hubp = new HappyVillagerParticle($center, $r, $g, $b, 1);
		for($yaw = 0, $y = $center->y; $y < $center->y + 4; $yaw += (M_PI * 2) / 20, $y += 1 / 20){
			$x = -sin($yaw) + $center->x;
			$z = cos($yaw) + $center->z;
			$hubp->setComponents($x, $y, $z);
			$level->addParticle($hubp);
		}
	}
}
