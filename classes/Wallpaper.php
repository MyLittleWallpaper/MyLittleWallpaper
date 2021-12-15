<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes;

use Exception;
use PDO;
use Tag;
use TagAspect;
use TagAuthor;
use TagColour;
use TagPlatform;

use function filter_var;
use function is_array;

use const FILTER_FLAG_PATH_REQUIRED;
use const FILTER_VALIDATE_INT;
use const FILTER_VALIDATE_URL;

require_once(ROOT_DIR . 'classes/Tag.php');

/**
 * Wallpaper class.
 */
class Wallpaper
{
    /**
     * @var int|null
     */
    private ?int $id = null;

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var string
     */
    private string $url = '';

    /**
     * @var string
     */
    private string $directDownloadLink = '';

    /**
     * @var string
     */
    private string $filename = '';

    /**
     * @var string
     */
    private string $fileId = '';

    /**
     * @var int
     */
    private int $width = 0;

    /**
     * @var int
     */
    private int $height = 0;

    /**
     * Unix timestamp
     * @var int
     */
    private int $timeAdded = 0;

    /**
     * @var string
     */
    private string $mime = '';

    /**
     * @var int
     */
    private int $clicks = 0;

    /**
     * @var int
     */
    private int $favourites = 0;

    /**
     * @var bool
     */
    private bool $hasAspect = true;

    /**
     * @var bool
     */
    private bool $hasResolution = true;

    /**
     * @var Tag[]
     */
    private array $tags = [];

    /**
     * @var TagAuthor[]
     */
    private array $authorTags = [];

    /**
     * @var TagAspect[]
     */
    private array $aspectTags = [];

    /**
     * @var TagPlatform[]
     */
    private array $platformTags = [];

    /**
     * @var TagColour[]
     */
    private array $colourTags = [];

    /**
     * @var Database
     */
    private Database $db;

    /**
     * @param array|null    $data
     * @param Database|null $db If null, looks for $GLOBALS['db']
     */
    public function __construct(?array $data = null, ?Database $db = null)
    {
        if (!($db instanceof Database)) {
            if (!isset($GLOBALS['db']) || !($GLOBALS['db'] instanceof Database)) {
                throw new Exception('No database connection found');
            }

            $this->db =& $GLOBALS['db'];
        } else {
            $this->db = $db;
        }
        if (!empty($data) && is_array($data)) {
            $this->bindData($data);
        }
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function bindDataById(int $id): bool
    {
        $result = $this->db->query("SELECT * FROM wallpaper WHERE id = ? LIMIT 1", [$id]);
        $return = false;
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $this->bindData($row);
            $return = true;
        }
        return $return;
    }

    /**
     * @param array $data wallpaper data
     *
     * @return void
     */
    public function bindData(array $data): void
    {
        if (!empty($data['id']) && filter_var($data['id'], FILTER_VALIDATE_INT) !== false) {
            $this->id = (int)$data['id'];
        }
        if (!empty($data['name'])) {
            $this->name = (string)$data['name'];
        }
        if (!empty($data['url'])) {
            $this->url = (string)$data['url'];
        }
        if (!empty($data['filename'])) {
            $this->filename = (string)$data['filename'];
        }
        if (!empty($data['file'])) {
            $this->fileId = (string)$data['file'];
        }
        if (!empty($data['width']) && filter_var($data['width'], FILTER_VALIDATE_INT) !== false) {
            $this->width = (int)$data['width'];
        }
        if (!empty($data['height']) && filter_var($data['height'], FILTER_VALIDATE_INT) !== false) {
            $this->height = (int)$data['height'];
        }
        if (!empty($data['mime'])) {
            $this->mime = (string)$data['mime'];
        }
        if (!empty($data['timeadded']) && filter_var($data['timeadded'], FILTER_VALIDATE_INT) !== false) {
            $this->timeAdded = (int)$data['timeadded'];
        }
        if (!empty($data['clicks']) && filter_var($data['clicks'], FILTER_VALIDATE_INT) !== false) {
            $this->clicks = (int)$data['clicks'];
        }
        if (!empty($data['favs']) && filter_var($data['favs'], FILTER_VALIDATE_INT) !== false) {
            $this->favourites = (int)$data['favs'];
        }
        if (!empty($data['no_aspect'])) {
            $this->hasAspect = false;
        }
        if (!empty($data['no_resolution'])) {
            $this->hasResolution = false;
        }
        if (!empty($data['direct_with_link'])) {
            $this->directDownloadLink = PROTOCOL . SITE_DOMAIN . PUB_PATH . 'c/' . CATEGORY . '/download/' .
                $this->fileId;
        } else {
            $this->directDownloadLink = $this->url;
        }
        $this->loadTags();
    }

    /**
     * Loads wallpaper tags.
     *
     * @return void
     */
    public function loadTags(): void
    {
        if ($this->id === null) {
            return;
        }

        // Author tags
        $sql    = "SELECT t.id, t.name, t.oldname FROM tag_artist t "
            . "JOIN wallpaper_tag_artist wt ON (wt.tag_artist_id = t.id) "
            . "WHERE wt.wallpaper_id = ? AND t.deleted = 0 "
            . "ORDER BY t.name";
        $result = $this->db->query($sql, [$this->id]);
        while ($tag = $result->fetch(PDO::FETCH_ASSOC)) {
            $this->authorTags[] = new TagAuthor($tag);
        }

        // Tags
        $sql    = "SELECT t.id, t.name, t.alternate, t.type FROM tag t "
            . "JOIN wallpaper_tag wt ON (wt.tag_id = t.id) "
            . "WHERE wt.wallpaper_id = ? "
            . "ORDER BY t.name";
        $result = $this->db->query($sql, [$this->id]);
        while ($tag = $result->fetch(PDO::FETCH_ASSOC)) {
            $this->tags[] = new Tag($tag);
        }

        // Platform tags
        $sql    = "SELECT t.id, t.name FROM tag_platform t "
            . "JOIN wallpaper_tag_platform wt ON (wt.tag_platform_id = t.id) "
            . "WHERE wt.wallpaper_id = ? "
            . "ORDER BY t.name";
        $result = $this->db->query($sql, [$this->id]);
        while ($tag = $result->fetch(PDO::FETCH_ASSOC)) {
            $this->platformTags[] = new TagPlatform($tag);
        }

        // Aspect tags
        if ($this->hasAspect) {
            $sql    = "SELECT t.id, t.name FROM tag_aspect t "
                . "JOIN wallpaper_tag_aspect wt ON (wt.tag_aspect_id = t.id) "
                . "WHERE wt.wallpaper_id = ? "
                . "ORDER BY t.name";
            $result = $this->db->query($sql, [$this->id]);
            while ($tag = $result->fetch(PDO::FETCH_ASSOC)) {
                $this->aspectTags[] = new TagAspect($tag);
            }
        }
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getDirectDownloadLink(): string
    {
        return $this->directDownloadLink;
    }

    /**
     * @return string
     */
    public function getDownloadLink(): string
    {
        return PROTOCOL . SITE_DOMAIN . PUB_PATH . 'c/' . CATEGORY . '/link/' . $this->fileId;
    }

    /**
     * @param int $type (1-3)
     *
     * @return string
     */
    public function getImageThumbnailLink(int $type = 1): string
    {
        return PROTOCOL . SITE_DOMAIN . PUB_PATH . 'images/r' . $type . '_' . $this->fileId . '.jpg';
    }

    /**
     * @return string
     */
    public function getImageLink(): string
    {
        return PROTOCOL . SITE_DOMAIN . PUB_PATH . 'images/o_' . $this->fileId . '.' .
            Format::fileExtension($this->filename);
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getFileId(): string
    {
        return $this->fileId;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->width . 'x' . $this->height;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * Unix timestamp.
     * @return int
     */
    public function getTimeAdded(): int
    {
        return $this->timeAdded;
    }

    /**
     * @return int
     */
    public function getClicks(): int
    {
        return $this->clicks;
    }

    /**
     * @return int
     */
    public function getFavourites(): int
    {
        return $this->favourites;
    }

    /**
     * @return bool
     */
    public function getHasAspect(): bool
    {
        return $this->hasAspect;
    }

    /**
     * @return bool
     */
    public function getHasResolution(): bool
    {
        return $this->hasResolution;
    }

    /**
     * @return Tag[]
     */
    public function getBasicTags(): array
    {
        return $this->tags;
    }

    /**
     * @return TagAspect[]
     */
    public function getAspectTags(): array
    {
        return $this->aspectTags;
    }

    /**
     * @return TagAuthor[]
     */
    public function getAuthorTags(): array
    {
        return $this->authorTags;
    }

    /**
     * @return TagPlatform[]
     */
    public function getPlatformTags(): array
    {
        return $this->platformTags;
    }

    /**
     * @return TagColour[]
     */
    public function getColourTags(): array
    {
        if (empty($this->colourTags) && $this->id !== null) {
            $this->loadColours();
        }
        return $this->colourTags;
    }

    /**
     * @return TagColour[]
     */
    public function getMajorColourTags(): array
    {
        if (empty($this->colourTags) && $this->id !== null) {
            $this->loadColours();
        }
        $return = [];
        if (!empty($this->colourTags)) {
            foreach ($this->colourTags as $colour) {
                if ($colour->getAmount() >= 20) {
                    $return[] = $colour;
                }
            }
        }
        return $return;
    }

    /**
     * @param string $val
     *
     * @return void
     */
    public function setName(string $val): void
    {
        $this->name = $val;
    }

    /**
     * @param string $val
     *
     * @return void
     */
    public function setUrl(string $val): void
    {
        if ($val !== '') {
            if (filter_var($val, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) !== false) {
                $this->url = $val;
            } else {
                throw new Exception('Invalid URL');
            }
        } else {
            $this->url = $val;
        }
    }

    /**
     * @param string $directDownloadLink
     *
     * @return void
     */
    public function setDirectDownloadLink(string $directDownloadLink): void
    {
        $this->directDownloadLink = $directDownloadLink;
    }

    /**
     * @param string $val
     *
     * @return void
     */
    public function setFilename(string $val): void
    {
        $this->filename = $val;
    }

    /**
     * @param string|null $val If null given, will be generated.
     *
     * @return void
     */
    public function setFile(string $val = null): void
    {
        if ($val === null) {
            $this->fileId = uniqid('', true);
        } else {
            $this->fileId = $val;
        }
    }

    /**
     * @param int $val
     *
     * @return void
     */
    public function setWidth(int $val): void
    {
        if (filter_var($val, FILTER_VALIDATE_INT) !== false) {
            $this->width = $val;
        }
    }

    /**
     * @param int $val
     *
     * @return void
     */
    public function setHeight(int $val): void
    {
        if (filter_var($val, FILTER_VALIDATE_INT) !== false) {
            $this->height = $val;
        }
    }

    /**
     * @param string $val
     *
     * @return void
     */
    public function setMime(string $val): void
    {
        $this->mime = $val;
    }

    /**
     * @param bool $val
     *
     * @return void
     */
    public function setHasAspect(bool $val): void
    {
        $this->hasAspect = $val;
    }

    /**
     * @param bool $val
     *
     * @return void
     */
    public function setHasResolution(bool $val): void
    {
        $this->hasResolution = $val;
    }

    /**
     * @param Tag $tag
     *
     * @return void
     */
    public function addBasicTag(Tag $tag): void
    {
        $this->tags[] = $tag;
    }

    /**
     * @param TagAspect $tag
     *
     * @return void
     */
    public function addAspectTag(TagAspect $tag): void
    {
        $this->aspectTags[] = $tag;
    }

    /**
     * @param TagAuthor $tag
     *
     * @return void
     */
    public function addAuthorTag(TagAuthor $tag): void
    {
        $this->authorTags[] = $tag;
    }

    /**
     * @param TagPlatform $tag
     *
     * @return void
     */
    public function addPlatformTag(TagPlatform $tag): void
    {
        $this->platformTags[] = $tag;
    }

    /**
     * @param string $colour
     * @param float  $amount
     *
     * @return void
     */
    public function addColourTag(string $colour, float $amount): void
    {
        if (is_numeric($amount) && $amount >= 0 && $amount <= 100) {
            $sql    = "SELECT colour FROM wallpaper_tag_colour_similar WHERE similar_colour = ? LIMIT 1";
            $result = $this->db->query($sql, [$colour]);
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $this->colourTags[] = new TagColour($row['colour'], $amount, $colour);
            }
        }
    }

    /**
     * @param Tag|string|int $tag Tag class, tag name or tag id
     *
     * @return void
     */
    public function removeBasicTag($tag): void
    {
        if ($tag instanceof Tag) {
            foreach ($this->tags as $key => $existing) {
                if ($existing->getId() === $tag->getId()) {
                    unset($this->tags[$key]);
                }
            }
        } elseif (is_int($tag)) {
            foreach ($this->tags as $key => $existing) {
                if ($existing->getId() === (int)$tag) {
                    unset($this->tags[$key]);
                }
            }
        } else {
            foreach ($this->tags as $key => $existing) {
                if ($existing->getName() === $tag) {
                    unset($this->tags[$key]);
                }
            }
        }
    }

    /**
     * @param TagAspect|string|int $tag TagAspect class, tag name or tag id
     *
     * @return void
     */
    public function removeAspectTag($tag): void
    {
        if ($tag instanceof TagAspect) {
            foreach ($this->aspectTags as $key => $existing) {
                if ($existing->getId() === $tag->getId()) {
                    unset($this->aspectTags[$key]);
                }
            }
        } elseif (is_int($tag)) {
            foreach ($this->aspectTags as $key => $existing) {
                if ($existing->getId() === (int)$tag) {
                    unset($this->aspectTags[$key]);
                }
            }
        } else {
            foreach ($this->aspectTags as $key => $existing) {
                if ($existing->getName() === $tag) {
                    unset($this->aspectTags[$key]);
                }
            }
        }
    }

    /**
     * @param TagAuthor|string|int $tag TagAuthor class, tag name or tag id
     *
     * @return void
     */
    public function removeAuthorTag($tag): void
    {
        if ($tag instanceof TagAuthor) {
            foreach ($this->authorTags as $key => $existing) {
                if ($existing->getId() === $tag->getId()) {
                    unset($this->authorTags[$key]);
                }
            }
        } elseif (is_int($tag)) {
            foreach ($this->authorTags as $key => $existing) {
                if ($existing->getId() === (int)$tag) {
                    unset($this->authorTags[$key]);
                }
            }
        } else {
            foreach ($this->authorTags as $key => $existing) {
                if ($existing->getName() === $tag) {
                    unset($this->authorTags[$key]);
                }
            }
        }
    }

    /**
     * @param TagPlatform|string|int $tag TagAuthor class, tag name or tag id
     *
     * @return void
     */
    public function removePlatformTag($tag): void
    {
        if ($tag instanceof TagPlatform) {
            foreach ($this->platformTags as $key => $existing) {
                if ($existing->getId() === $tag->getId()) {
                    unset($this->authorTags[$key]);
                }
            }
        } elseif (is_int($tag)) {
            foreach ($this->platformTags as $key => $existing) {
                if ($existing->getId() === (int)$tag) {
                    unset($this->authorTags[$key]);
                }
            }
        } else {
            foreach ($this->platformTags as $key => $existing) {
                if ($existing->getName() === $tag) {
                    unset($this->authorTags[$key]);
                }
            }
        }
    }

    /**
     * @param string $colour
     *
     * @return void
     */
    public function removeColourTag(string $colour): void
    {
        $tempColour = new TagColour($colour);
        foreach ($this->colourTags as $key => $colour_class) {
            if ($tempColour->getSimilarColourHex() === $colour_class->getSimilarColourHex()) {
                unset($this->colourTags[$key]);
            }
        }
    }

    /**
     * @param int $userId
     *
     * @return bool
     */
    public function getIsFavourite(int $userId): bool
    {
        if (!empty($this->id)) {
            $sql    = "SELECT wallpaper_id FROM wallpaper_fav WHERE wallpaper_id = ? AND user_id = ?";
            $result = $this->db->query($sql, [$this->id, $userId]);
            while ($favData = $result->fetch(PDO::FETCH_ASSOC)) {
                if ((int)$favData['wallpaper_id'] == $this->id) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return void
     */
    private function loadColours(): void
    {
        if (empty($this->colourTags) && $this->id !== null) {
            $sql    = "SELECT clt.colour, clwt.amount, clwt.tag_colour similar_colour FROM wallpaper_tag_colour clwt "
                . "JOIN wallpaper_tag_colour_similar clt ON (clwt.tag_colour = clt.similar_colour) "
                . "WHERE clwt.wallpaper_id = ? "
                . "ORDER BY clwt.amount DESC, clt.colour ASC";
            $result = $this->db->query($sql, [$this->id]);
            while ($colour = $result->fetch(PDO::FETCH_ASSOC)) {
                $this->colourTags[] = new TagColour($colour['colour'], $colour['amount'], $colour['similar_colour']);
            }
        }
    }
}
