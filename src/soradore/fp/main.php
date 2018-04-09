<?php



namespace soradore\fp;


/* Base */
use pocketmine\plugin\PluginBase;
use pocketmine\Player;

/* Events */
use pocketmine\event\Listener;


/* Level and Math */
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;


use pocketmine\block\Block;
use pocketmine\item\T\Item;

use pocketmine\network\mcpe\protocol\PlayerActionPacket;



class main extends PluginBase implements Listener 
{


    /*************************** Setting Area */



    /**
     *   int   Block Id
     */
    define("JOIN_BLOCK", 2);




    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->baseInit();
    }




    public function baseInit(){
        $this->canvasBlocks = [
                               "Wool" => 35,
                               ];
        $this->status = [];
        $this->players = [];
        $this->game = false;
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



    /**
     * @var    Player $player 
     * @return bool
     */
    
    public function isPlayer(Player $player){
        return in_array($player, $this->players, true);
    }


    

    public function isJoinBlock(Block $block){
        return $block->getId() == self::JOIN_BLOCK;
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
        $this->setPlayerStatus($player, true);
    }




    public function onBreak(BlockBreakEvent $ev){
        $ev->setCancelled();
    }



    public function onPlace(BlockPlaceEvent $ev){
        $ev->setCancelled();
    }




    public function onTouch(PlayerInteractEvent $ev){
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        if($this->isJoinBlock($block)){
            if(!$this->isPlayer($player)){
                $this->addPlayer($player);
            }
        }
    }



}

