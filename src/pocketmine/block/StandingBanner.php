<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\block;

use Ds\Deque;
use pocketmine\block\utils\BannerPattern;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Banner as ItemBanner;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Banner as TileBanner;
use function assert;
use function floor;

class StandingBanner extends Transparent{

	/** @var int */
	protected $rotation = 0;

	/** @var DyeColor */
	protected $baseColor;

	/** @var Deque|BannerPattern[] */
	protected $patterns;

	public function __construct(BlockIdentifier $idInfo, string $name){
		parent::__construct($idInfo, $name);
		$this->baseColor = DyeColor::BLACK();
		$this->patterns = new Deque();
	}

	public function __clone(){
		$this->patterns = $this->patterns->map(function(BannerPattern $pattern) : BannerPattern{ return clone $pattern; });
	}

	protected function writeStateToMeta() : int{
		return $this->rotation;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->rotation = $stateMeta;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->level->getTile($this);
		if($tile instanceof TileBanner){
			$this->baseColor = $tile->getBaseColor();
			$this->setPatterns($tile->getPatterns());
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->level->getTile($this);
		assert($tile instanceof TileBanner);
		$tile->setBaseColor($this->baseColor);
		$tile->setPatterns($this->patterns);
	}

	public function getHardness() : float{
		return 1;
	}

	public function isSolid() : bool{
		return false;
	}

	/**
	 * TODO: interface method? this is only the BASE colour...
	 * @return DyeColor
	 */
	public function getColor() : DyeColor{
		return $this->baseColor;
	}

	/**
	 * @return Deque|BannerPattern[]
	 */
	public function getPatterns() : Deque{
		return $this->patterns;
	}

	/**
	 * @param Deque|BannerPattern[] $patterns
	 */
	public function setPatterns(Deque $patterns) : void{
		$checked = $patterns->filter(function($v){ return $v instanceof BannerPattern; });
		if($checked->count() !== $patterns->count()){
			throw new \TypeError("Deque must only contain " . BannerPattern::class . " objects");
		}
		$this->patterns = $checked;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return null;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof ItemBanner){
			$this->baseColor = $item->getColor();
			$this->setPatterns($item->getPatterns());
		}
		if($face !== Facing::DOWN){
			if($face === Facing::UP and $player !== null){
				$this->rotation = ((int) floor((($player->yaw + 180) * 16 / 360) + 0.5)) & 0x0f;
				return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
			}

			//TODO: awful hack :(
			$wallBanner = BlockFactory::get(Block::WALL_BANNER, $face);
			assert($wallBanner instanceof WallBanner);
			$wallBanner->baseColor = $this->baseColor;
			$wallBanner->setPatterns($this->patterns);
			return $this->getLevel()->setBlock($blockReplace, $wallBanner);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->getId() === self::AIR){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$drop = ItemFactory::get(Item::BANNER, $this->baseColor->getInvertedMagicNumber());
		if($drop instanceof ItemBanner and !$this->patterns->isEmpty()){
			$drop->setPatterns($this->patterns);
		}

		return [$drop];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
