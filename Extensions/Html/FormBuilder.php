<?php
namespace Elboletaire\Extensions\Html;

use Illuminate\Html;

class FormBuilder extends Html\FormBuilder
{
	/**
	 * Set to true when a form has been opened
	 *
	 * @var boolean
	 */
	protected $open = false;

	/**
	 * Generates a proper delete button for forms
	 *
	 * @param  string $route      The route where should point to
	 * @param  int    $id         The id of the element to be deleted
	 * @param  string $text       The text of the button. If not set it will be "delete";
	 * @param  array  $attributes Any other attribute that you may want set
	 * @return string
	 */
	public function delete($route, $id, $text = null, array $attributes = array())
	{
		$method = 'delete';
		empty($text) && $text = $method;

		$url = route($route, $id);

		if (!isset($attributes['confirm'])) {
			$confirm = 'Are you sure to remove #%d?';
		} else {
			$confirm = $attributes['confirm'];
		}

		if ($confirm !== false) {
			$attributes = array('onclick' => 'return confirm(' . json_encode(sprintf($confirm, $id)) . ');') + $attributes;
		}

		$form = '';
		if ($this->open) {
			$attributes = array('formaction' => $url, 'formmethod' => 'post', 'name' => '_method', 'value' => 'DELETE') + $attributes;
		} else {
			$form = $this->open(compact('url', 'method'));
		}
		$form .= $this->button($text, array_merge(array('type' => 'submit'), $attributes));
		if (!$this->open) {
			$form .= $this->close();
		}
		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function open(array $options = array())
	{
		$this->open = true;
		return parent::open($options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function close()
	{
		$this->open = false;
		return /*$this->hidden('referrer', \URL::previous()) . */parent::close();
	}
}
