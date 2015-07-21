<?php

trait MessageTrait
{
    protected $version = '1.1';
    protected $headers = array();
    protected $stream;

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return self
     */
    public function withProtocolVersion($version)
    {
        if ($version === $this->version) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->version = $version;
        return $newInstance;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        $name = trim(strtolower($name));

        $headers = $this->getHeader($name);
        return (count($headers) > 0);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        $name = trim(strtolower($name));
        if (isset ($this->headers[$name])) {
            return $this->headers[$name];
        }

        return [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        $name = trim(strtolower($name));

        if (!isset ($this->headers[$name]))
            return "";

        return implode(',', $this->headers[$name]);
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        $value = !is_array($value) ? [$value] : $value;

        $newInstance = clone $this;
        $newInstance->headers[$name] = $value;

        return $newInstance;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        $name = trim(strtolower($name));
        $value = !is_array($value) ? [$value] : $value;
        $newInstance = clone $this;

        if (isset ($newInstance->headers[$name])) {
            $newInstance->headers[$name] = array_merge($newInstance->headers[$name], $value);
        } else {
            $newInstance->headers[$name] = $value;
        }

        return $newInstance;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return self
     */
    public function withoutHeader($name)
    {
        $name = trim(strtolower($name));
        $newInstance = clone $this;

        unset ($newInstance->headers[$name]);
        return $newInstance;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->stream;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return self
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $newInstance = clone $this;
        $newInstance->stream = $body;

        return $newInstance;
    }

    protected function setHeaders ( array $headers )
    {
        $this->headers = array ( );

        foreach ( $headers as $name => $value )
        {
            $name = trim ( strtolower ( $name ) );

            $value = !is_array ( $value ) ? [$value] : $value;

            if ( isset ( $this->headers[$name] ) )
            {
                $this->headers[$name] = array_merge ( $this->headers[$name], $value );
            }
            else
            {
                $this->headers[$name] = $value;
            }
        }
    }
}

?>