<?php

namespace InstagramAPI\Media\Photo;

use InstagramAPI\Media\Geometry\Dimensions;
use InstagramAPI\Media\Geometry\Rectangle;
use InstagramAPI\Media\InstagramMedia;
use InstagramAPI\Utils;

/**
 * Automatically prepares a photo file according to Instagram's rules.
 *
 * @property PhotoDetails $_details
 */
class InstagramPhoto extends InstagramMedia
{
    /**
     * Output JPEG quality.
     *
     * This value was chosen because 100 is very wasteful. And don't tweak this
     * number, because the JPEG quality number is actually totally meaningless
     * (it is non-standardized) and Instagram can't even read it from the file.
     * They have no idea what quality we've used, and it can be harmful to go
     * lower since different JPEG compressors (such as PHP's implementation) use
     * different quality scales and are often awful at lower qualities! We know
     * that PHP's JPEG quality at 95 is great, so there's no reason to lower it.
     *
     * @var int
     */
    const JPEG_QUALITY = 95;

    /**
     * Constructor.
     *
     * @param string $inputFile Path to an input file.
     * @param array  $options   An associative array of optional parameters.
     *
     * @throws \InvalidArgumentException
     *
     * @see InstagramMedia::__construct() description for the list of parameters.
     */
    public function __construct(
        $inputFile,
        array $options = [])
    {
        parent::__construct($inputFile, $options);
        $this->_details = new PhotoDetails($this->_inputFile);
    }

    /** {@inheritdoc} */
    protected function _isMod2CanvasRequired()
    {
        return false;
    }

    /** {@inheritdoc} */
    protected function _createOutputFile(
        Rectangle $srcRect,
        Rectangle $dstRect,
        Dimensions $canvas)
    {
        $outputFile = null;

        try {
            // Attempt to process the input file.
            $resource = $this->_loadImage();

            try {
                $output = $this->_processResource($resource, $srcRect, $dstRect, $canvas);
            } finally {
                @imagedestroy($resource);
            }

            // Write the result to disk.
            try {
                // Prepare output file.
                $outputFile = Utils::createTempFile($this->_tmpPath, 'IMG');

                if (!imagejpeg($output, $outputFile, self::JPEG_QUALITY)) {
                    throw new \RuntimeException('Failed to create JPEG image file.');
                }
            } finally {
                @imagedestroy($output);
            }
        } catch (\Exception $e) {
            if ($outputFile !== null && is_file($outputFile)) {
                @unlink($outputFile);
            }

            throw $e; // Re-throw.
        }

        return $outputFile;
    }

    /**
     * Loads image into a resource.
     *
     * @throws \RuntimeException
     *
     * @return resource
     */
    protected function _loadImage()
    {
        // Read the correct input file format.
        switch ($this->_details->getType()) {
            case IMAGETYPE_JPEG:
                $resource = imagecreatefromjpeg($this->_inputFile);
                break;
            case IMAGETYPE_PNG:
                $resource = imagecreatefrompng($this->_inputFile);
                break;
            case IMAGETYPE_GIF:
                $resource = imagecreatefromgif($this->_inputFile);
                break;
            default:
                throw new \RuntimeException('Unsupported image type.');
        }
        if ($resource === false) {
            throw new \RuntimeException('Failed to load image.');
        }

        return $resource;
    }

    /**
     * @param resource   $source  The original image loaded as a resource.
     * @param Rectangle  $srcRect Rectangle to copy from the input.
     * @param Rectangle  $dstRect Destination place and scale of copied pixels.
     * @param Dimensions $canvas  The size of the destination canvas.
     *
     * @throws \Exception
     * @throws \RuntimeException
     *
     * @return resource
     */
    protected function _processResource(
        $source,
        Rectangle $srcRect,
        Rectangle $dstRect,
        Dimensions $canvas
    ) {
        // If our input image pixels are stored rotated, swap all coordinates.
        if ($this->_details->hasSwappedAxes()) {
            $srcRect = $srcRect->withSwappedAxes();
            $dstRect = $dstRect->withSwappedAxes();
            $canvas = $canvas->withSwappedAxes();
        }
        
        if ($dstRect->getWidth() > $dstRect->getHeight()) {
            $newwidth = 720;
            $divisor = $dstRect->getWidth() / 720;
            $newheight = floor( $dstRect->getHeight() / $divisor);
        } else {
            $newheight = 720;
            $divisor = $dstRect->getHeight() / 720;
            $newwidth = floor( $dstRect->getWidth() / $divisor );
        }

        // Create an output canvas with our desired size.
        $output = imagecreatetruecolor($newwidth, $newheight);
        if ($output === false) {
            throw new \RuntimeException('Failed to create output image.');
        }

        // Fill the output canvas with our background color.
        // NOTE: If cropping, this is just to have a nice background in
        // the resulting JPG if a transparent image was used as input.
        // If expanding, this will be the color of the border as well.
        $bgColor = imagecolorallocate($output, $this->_bgColor[0], $this->_bgColor[1], $this->_bgColor[2]);
        if ($bgColor === false) {
            throw new \RuntimeException('Failed to allocate background color.');
        }
        if (imagefilledrectangle($output, 0, 0, $newheight - 1, $newheight - 1, $bgColor) === false) {
            throw new \RuntimeException('Failed to fill image with background color.');
        }


        // Copy the resized (and resampled) image onto the new canvas.
        if (imagecopyresampled(
                $output, $source,
                $dstRect->getX(), $dstRect->getY(),
                $srcRect->getX(), $srcRect->getY(),
                $newwidth, $newheight,
                $srcRect->getWidth(), $srcRect->getHeight()
            ) === false) {
            throw new \RuntimeException('Failed to resample image.');
        }

        // Handle image rotation.
        $output = $this->_rotateResource($output, $bgColor);

        return $output;
    }

    /**
     * Wrapper for PHP's imagerotate function.
     *
     * @param resource $original
     * @param int      $bgColor
     *
     * @throws \RuntimeException
     *
     * @return resource
     */
    protected function _rotateResource(
        $original,
        $bgColor)
    {
        $angle = 0;
        $flip = null;
        // Find out angle and flip.
        if ($this->_details->hasSwappedAxes()) {
            if ($this->_details->isHorizontallyFlipped() && $this->_details->isVerticallyFlipped()) {
                $angle = -90;
                $flip = IMG_FLIP_HORIZONTAL;
            } elseif ($this->_details->isHorizontallyFlipped()) {
                $angle = -90;
            } elseif ($this->_details->isVerticallyFlipped()) {
                $angle = 90;
            } else {
                $angle = -90;
                $flip = IMG_FLIP_VERTICAL;
            }
        } else {
            if ($this->_details->isHorizontallyFlipped() && $this->_details->isVerticallyFlipped()) {
                $flip = IMG_FLIP_BOTH;
            } elseif ($this->_details->isHorizontallyFlipped()) {
                $flip = IMG_FLIP_HORIZONTAL;
            } elseif ($this->_details->isVerticallyFlipped()) {
                $flip = IMG_FLIP_VERTICAL;
            } else {
                // Do nothing.
            }
        }

        // Flip the image resource if needed. Does not create a new resource.
        if ($flip !== null && imageflip($original, $flip) === false) {
            throw new \RuntimeException('Failed to flip image.');
        }

        // Return original resource if no rotation is needed.
        if ($angle === 0) {
            return $original;
        }

        // Attempt to create a new, rotated image resource.
        $result = imagerotate($original, $angle, $bgColor);
        if ($result === false) {
            throw new \RuntimeException('Failed to rotate image.');
        }

        // Destroy the original resource since we'll return the new resource.
        @imagedestroy($original);

        return $result;
    }
}
