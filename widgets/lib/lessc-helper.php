<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 30/4/15
 * Time: 8:16 PM
 */

class lessc_helper {

	/**
	 * @var object lessc
	 */
	public $lessc_parser;

	/**
	 * @param object $lessc_parser Instance of class lessc_parser
	 */
	public function __construct( $lessc_parser ){

		$this->lessc_parser = $lessc_parser;

	}

	/**
	 * Parse a single chunk off the head of the buffer and append it to the
	 * current parse environment.
	 * Returns false when the buffer is empty, or when there is an error.
	 *
	 * This function is called repeatedly until the entire document is
	 * parsed.
	 *
	 * This parser is most similar to a recursive descent parser. Single
	 * functions represent discrete grammatical rules for the language, and
	 * they are able to capture the text that represents those rules.
	 *
	 * Consider the function lessc::keyword(). ( all parse functions are
	 * structured the same )
	 *
	 * The function takes a single reference argument. When calling the
	 * function it will attempt to match a keyword on the head of the buffer.
	 * If it is successful, it will place the keyword in the referenced
	 * argument, advance the position in the buffer, and return true. If it
	 * fails then it won't advance the buffer and it will return false.
	 *
	 * All of these parse functions are powered by lessc::match(), which behaves
	 * the same way, but takes a literal regular expression. Sometimes it is
	 * more convenient to use match instead of creating a new function.
	 *
	 * Because of the format of the functions, to parse an entire string of
	 * grammatical rules, you can chain them together using &&.
	 *
	 * But, if some of the rules in the chain succeed before one fails, then
	 * the buffer position will be left at an invalid state. In order to
	 * avoid this, lessc::seek() is used to remember and set buffer positions.
	 *
	 * Before parsing a chain, use $s = $this->lessc_parser->seek() to remember the current
	 * position into $s. Then if a chain fails, use $this->lessc_parser->seek( $s ) to
	 * go back where we started.
	 */
	public function parseChunk() {
		if ( empty( $this->lessc_parser->buffer ) ) return false;
		$s = $this->lessc_parser->seek();

		// setting a property
		if ( $this->lessc_parser->keyword( $key ) && $this->lessc_parser->assign() &&
			$this->lessc_parser->propertyValue( $value, $key ) && $this->lessc_parser->end() )
		{
			$this->lessc_parser->append( array( 'assign', $key, $value ), $s );
			return true;
		} else {
			$this->lessc_parser->seek( $s );
		}


		// look for special css blocks
		if ( $this->lessc_parser->literal( '@', false ) ) {
			$this->lessc_parser->count--;

			// media
			if ( $this->lessc_parser->literal( '@media' ) ) {
				if ( ( $this->lessc_parser->mediaQueryList( $mediaQueries ) || true )
					&& $this->lessc_parser->literal( '{' ) )
				{
					$media = $this->lessc_parser->pushSpecialBlock( "media" );
					$media->queries = is_null( $mediaQueries ) ? array() : $mediaQueries;
					return true;
				} else {
					$this->lessc_parser->seek( $s );
					return false;
				}
			}

			if ( $this->lessc_parser->literal( "@", false ) && $this->lessc_parser->keyword( $dirName ) ) {
				if ( $this->lessc_parser->isDirective( $dirName, $this->lessc_parser->blockDirectives ) ) {
					if ( ( $this->lessc_parser->openString( "{", $dirValue, null, array( ";" ) ) || true ) &&
						$this->lessc_parser->literal( "{" ) )
					{
						$dir = $this->lessc_parser->pushSpecialBlock( "directive" );
						$dir->name = $dirName;
						if ( isset( $dirValue ) ) $dir->value = $dirValue;
						return true;
					}
				} elseif ( $this->lessc_parser->isDirective( $dirName, $this->lessc_parser->lineDirectives ) ) {
					if ( $this->lessc_parser->propertyValue( $dirValue ) && $this->lessc_parser->end() ) {
						$this->lessc_parser->append( array( "directive", $dirName, $dirValue ) );
						return true;
					}
				}
			}

			$this->lessc_parser->seek( $s );
		}

		// setting a variable
		if ( $this->lessc_parser->variable( $var ) && $this->lessc_parser->assign() &&
			$this->lessc_parser->propertyValue( $value ) && $this->lessc_parser->end() )
		{
			$this->lessc_parser->append( array( 'assign', $var, $value ), $s );
			return true;
		} else {
			$this->lessc_parser->seek( $s );
		}

		if ( $this->lessc_parser->import( $importValue ) ) {
			$this->lessc_parser->append( $importValue, $s );
			return true;
		}

		// opening parametric mixin
		if ( $this->lessc_parser->tag( $tag, true ) && $this->lessc_parser->argumentDef( $args, $isVararg ) &&
			( $this->lessc_parser->guards( $guards ) || true ) &&
			$this->lessc_parser->literal( '{' ) )
		{
			$block = $this->lessc_parser->pushBlock( $this->lessc_parser->fixTags( array( $tag ) ) );
			$block->args = $args;
			$block->isVararg = $isVararg;
			if ( ! empty( $guards ) ) $block->guards = $guards;
			return true;
		} else {
			$this->lessc_parser->seek( $s );
		}

		// opening a simple block
		if ( $this->lessc_parser->tags( $tags ) && $this->lessc_parser->literal( '{' ) ) {
			$tags = $this->lessc_parser->fixTags( $tags );
			$this->lessc_parser->pushBlock( $tags );
			return true;
		} else {
			$this->lessc_parser->seek( $s );
		}

		// closing a block
		if ( $this->lessc_parser->literal( '}', false ) ) {
			try {
				$block = $this->lessc_parser->pop();
			} catch ( exception $e ) {
				$this->lessc_parser->seek( $s );
				$this->lessc_parser->throwError( $e->getMessage() );
			}

			$hidden = false;
			if ( is_null( $block->type ) ) {
				$hidden = true;
				if ( ! isset( $block->args ) ) {
					foreach ( $block->tags as $tag ) {
						if ( ! is_string( $tag ) || $tag{0} != $this->lessc_parser->lessc->mPrefix ) {
							$hidden = false;
							break;
						}
					}
				}

				foreach ( $block->tags as $tag ) {
					if ( is_string( $tag ) ) {
						$this->lessc_parser->env->children[$tag][] = $block;
					}
				}
			}

			if ( ! $hidden ) {
				$this->lessc_parser->append( array( 'block', $block ), $s );
			}

			// this is done here so comments aren't bundled into he block that
			// was just closed
			$this->lessc_parser->whitespace();
			return true;
		}

		// mixin
		if ( $this->lessc_parser->mixinTags( $tags ) &&
			( $this->lessc_parser->argumentValues( $argv ) || true ) &&
			( $this->lessc_parser->keyword( $suffix ) || true ) && $this->lessc_parser->end() )
		{
			$tags = $this->lessc_parser->fixTags( $tags );
			$this->lessc_parser->append( array( 'mixin', $tags, $argv, $suffix ), $s );
			return true;
		} else {
			$this->lessc_parser->seek( $s );
		}

		// spare ;
		if ( $this->lessc_parser->literal( ';' ) ) return true;

		return false; // got nothing, throw error
	}

}