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

namespace Implactor\npc\bot;

use pocketmine\entity\Entity;
use pocketmine\scheduler\Task;
use pocketmine\math\Vector3;

use Implactor\npc\bot\BotTask;
use Implactor\npc\bot\BotHuman;
use Implactor\MainIR;

class BotWalkingTask extends Task{

	/** @var MainIR $plugin */
	/** @var Entity $entity */
	private $plugin, $entity;

	public function __construct(MainIR $plugin, Entity $entity){
		$this->plugin = $plugin;
		$this->entity = $entity;
		parent::__construct($plugin);
	}

	public function onRun(int $tick): void{
			$entity = $this->entity;
			$distance = 0.5;

			if($entity instanceof BotHuman){
				switch($entity->getDirection()){
					case 0:
					$entity->setMotion(new Vector3($distance, 0, 0));
					break;
					case 1:
					$entity->setMotion(new Vector3(0, 0, $distance));
					break;
					case 2:
					$entity->setMotion(new Vector3(-$distance, 0, 0));
					break;
					case 3:
					$entity->setMotion(new Vector3(0, 0, -$distance));
					break;
				}
			}
		}
	}