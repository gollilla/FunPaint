<?php



namespace soradore\fp;


/* Base */
use pocketmine\plugin\PluginBase;

/* Events */
use pocketmine\event\Listener;


/* Level and Math */
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\block\Block;
use pocketmine\item\T\Item;

use pocketmine\network\mcpe\protocol\PlayerActionPacket;

class main extends PluginBase implements Listener{


    /*************************** Setting Area */




    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->init();
    }




    public function init(){
        $this->canvasBlocks = [
                               "Wool" => 35,
                               ];
    }




    /*************************** Working Area */





    public function onAction(Player $player, int $x, int $y, int $z){
        if(!$this->getPlayerStatus($player)) return false;
        $level = $player->getLevel();
        $id = $level->getBlockIdAt($x, $y, $z);
        $color = $this->getPlayerHandColor($player);
        if($this->isCanvas($id)){
            $level->setBlock(new Vector3($x, $y, $z), $color);
        }
    }



    /**
     * @var    int  ItemId
     * @return bool
     */

    public function isCanvas(int $id){
        $canPaint = $this->getCanvasBlocks();
        return in_array($id, $canPaint);
    }



    /**
     * @var    Player player
     * @return Block  
     */

    public function getPlayerHandColor(Player $player){
        $item = $player->getInventory()->getItemInHand();
        $id = $item->getId();
        $meta = $item->getDamage();
        if($this->isCanvas($id)){
            return Block::get($id, $meta);
        }
    }




    /**
     * @return Array
     */

    public function getCanvasBlocks(){
        return $this->canvasBlocks;
    }




    /**
     * @var    Player player
     * @return bool
     */

    public function getPlayerStatus(Player $player){
        return $this->status[$player->getName()];
    }


    

    public function setPlayerStatus(Player $player, bool $val = false){
        $this->status[$player->getName()] = $val;
    }



    /**************************** Event Area */




    public function onReceive(DataPacketReceiveEvent $ev){
        $pk = $ev->getPacket();
        if($pk instanceof PlayerActionPacket){
            $type = $pk->action;
            if($type == PlayerActionPacket::ACTION_START_BREAK){
                $player = $ev->getPlayer();
                $this->onAction($player, $pk->x, $pk->y, $pk->z);
                return true;
            }
        }
    }




    public function onJoin(PlayerJoinEvent $ev){
        $player = $ev->getPlayer();
        $this->setPlayerStatus($player);
    }



}

