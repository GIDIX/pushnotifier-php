<?php
	namespace GIDIX\PushNotifier\SDK;

	/**
	 * A PushNotifier device
	 *
	 * @author  bluefirex
	 * @version 1.0 <January 2018>
	 */
	class Device {
		protected $id;
		protected $title;
		protected $model;
		protected $image;

		public function __construct(string $id, string $title, string $model, string $image) {
			$this->id = $id;
			$this->title = $title;
			$this->model = $model;
			$this->image = $image;
		}

		/**
		 * Get the ID of the device [not numeric!]
		 *
		 * @return string
		 */
		public function getID(): string {
			return $this->id;
		}

		/**
		 * Get the user-defined title of the device
		 *
		 * @return string
		 */
		public function getTitle(): string {
			return $this->title;
		}

		/**
		 * Get the model name of the device
		 *
		 * @return string
		 */
		public function getModel(): string {
			return $this->model;
		}

		/**
		 * Get the URL to an image of the device
		 *
		 * @return string
		 */
		public function getImage(): string {
			return $this->image;
		}
	}