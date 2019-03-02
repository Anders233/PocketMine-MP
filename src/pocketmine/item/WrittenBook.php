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

namespace pocketmine\item;

use pocketmine\nbt\tag\CompoundTag;

class WrittenBook extends WritableBook{

	public const GENERATION_ORIGINAL = 0;
	public const GENERATION_COPY = 1;
	public const GENERATION_COPY_OF_COPY = 2;
	public const GENERATION_TATTERED = 3;

	public const TAG_GENERATION = "generation"; //TAG_Int
	public const TAG_AUTHOR = "author"; //TAG_String
	public const TAG_TITLE = "title"; //TAG_String

	/** @var int */
	private $generation = self::GENERATION_ORIGINAL;
	/** @var string */
	private $author = "";
	/** @var string */
	private $title = "";

	public function __construct(){
		Item::__construct(self::WRITTEN_BOOK, 0, "Written Book");
	}

	public function getMaxStackSize() : int{
		return 16;
	}

	/**
	 * Returns the generation of the book.
	 * Generations higher than 1 can not be copied.
	 *
	 * @return int
	 */
	public function getGeneration() : int{
		return $this->generation;
	}

	/**
	 * Sets the generation of a book.
	 * TODO: make this fluent
	 *
	 * @param int $generation
	 */
	public function setGeneration(int $generation) : void{
		if($generation < 0 or $generation > 3){
			throw new \InvalidArgumentException("Generation \"$generation\" is out of range");
		}
		$this->generation = $generation;
	}

	/**
	 * Returns the author of this book.
	 * This is not a reliable way to get the name of the player who signed this book.
	 * The author can be set to anything when signing a book.
	 *
	 * @return string
	 */
	public function getAuthor() : string{
		return $this->author;
	}

	/**
	 * Sets the author of this book.
	 * TODO: make this fluent
	 * @param string $authorName
	 */
	public function setAuthor(string $authorName) : void{
		$this->author = $authorName;
	}

	/**
	 * Returns the title of this book.
	 *
	 * @return string
	 */
	public function getTitle() : string{
		return $this->title;
	}

	/**
	 * Sets the author of this book.
	 * TODO: make this fluent
	 * @param string $title
	 */
	public function setTitle(string $title) : void{
		$this->title = $title;
	}

	public function deserializeCompoundTag(CompoundTag $tag) : void{
		parent::deserializeCompoundTag($tag);
		$this->generation = $tag->getInt(self::TAG_GENERATION, $this->generation);
		$this->author = $tag->getString(self::TAG_AUTHOR, $this->author);
		$this->title = $tag->getString(self::TAG_TITLE, $this->title);
	}

	public function serializeCompoundTag(CompoundTag $tag) : void{
		parent::serializeCompoundTag($tag);
		$tag->setInt(self::TAG_GENERATION, $this->generation);
		$tag->setString(self::TAG_AUTHOR, $this->author);
		$tag->setString(self::TAG_TITLE, $this->title);
	}
}
