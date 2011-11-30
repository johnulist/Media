<?PHP
/**
 * @author <joel@razorit.com>
 * @property Media $Media
 */
class MediaController extends AppController {


	var $name = 'Media';
	#var $uid;
	#var $uses = array('');
	var $allowedActions = array('index', 'view', 'notification', 'stream');
        #public $helpers = array('Ratings.Rating'); # will be loaded regardless
        public $components = array('Ratings.Ratings');


	/*
         * kinda expects URL to be: /media/media/index/(audio|video)
         * shows media of the type passed in the request
	*/
	public function index() {
            #debug($this->request->pass);
            if(isset($this->request->pass[0])) {
                $mediaType = $this->request->pass[0];
            }
		$allMedia = $this->Media->find('all', array(
				'conditions' => array(
                                    'Media.filename !=' => '',
                                    'Media.type' => $mediaType
                                )
			));

		$this->set('media', $allMedia);
	}//index()


	public function add() {
		#debug($this->request->data);
		#debug($this->request->params);
		if($this->request->data) {
                        $this->request->data['User']['id'] = $this->Auth->user('id');
			#debug($this->request->data);break;
			if ($this->Media->save($this->request->data)) {
				$this->Session->setFlash('Media saved and being encoded.');
                                #$this->redirect('/media/media/edit/'.$this->Media->id);
                                $this->redirect('/media/my/');
			} else {
				$this->Session->setFlash('Invalid Upload.');
			}
		}

	}//upload()


        /**
         *
         * @param char $mediaID The UUID of the media in question.
         */
        public function edit($mediaID = null) {
            /** @todo Finish up the edit code.. put in thumbnails probably */
		if($mediaID) {
			$theMedia = $this->Media->findById($mediaID);
			$this->set('theMedia', $theMedia);
		}
        }//edit()


        /**
         *
         * @param char $mediaID The UUID of the media in question.
         */
        public function view($mediaID = null) {

		if($mediaID) {
                    
                    // Use this to save the Overall Rating to Media.rating
                    #$this->Media->calculateRating($mediaID, 'rating');
                    
                    $theMedia = $this->Media->findById($mediaID);
                    
                    // Use these two lines to get the Overall Rating on the fly
                    $theMediaRating = $this->Media->calculateRating($mediaID);
                    $theMedia = array_merge($theMediaRating, $theMedia);

                    $this->pageTitle = $theMedia['Media']['title'];
                    $this->set('theMedia', $theMedia);
                    
		}

	}//view()

        
	public function my() {
            $userID = ($this->Auth->user('id')) ? $this->Auth->user('id') : false;
            if($userID) {
                $allMedia = $this->Media->find('all', array(
                    'conditions' => array(
                        'Media.user_id' => $userID,
                        #'Media.type' => $mediaType
                        )
                    ));

                $this->set('media', $allMedia);
            } else {
                $this->redirect('/');
            }
	}//my()
        

        /**
         * @todo parse the response and activate the video when it's encoding job is completed
         */
	public function notification() {

                $data = $this->request->input('json_decode');
                debug($data);break;
		if($data) {

#			$this->Media->notify($data);
			// zencoder is notifying us that a Job is complete
			if($data['output']['state'] == 'finished') {

				#echo "w00t!\n";

				// If you're encoding to multiple outputs and only care when all of the outputs are finished
				// you can check if the entire job is finished.
				if($$data['job']['state'] == 'finished') {
					echo "Dubble w00t!\n";

					// find this zencoder_job_id
					$encoder_job = $this->Media->find('first', array('conditions' => array('Media.zen_job_id' => $data['job']['id'])));
					# TODO : allow for multiple output URL's....
					$encoder_job['Media']['filename'] = $data['output']['url'];
					#$this->Media->save($encoder_job);
				}

			} elseif($data['output']['state'] == 'cancelled') {
				echo "Cancelled!\n";
			} else {
				echo "Fail!\n";
				debug($data);
				echo $data['output']['error_message']."\n";
				echo $data['output']['error_link'];
			}

		}//if($outputID)

		$this->render(false);

	}//notification()


        /**
         * This action can stream or download a media file.
         * Expected Use: /media/media/stream/{UUID}/{FORMAT}
         * @param char $mediaID The UUID of the media in question.
         */
        function stream($mediaID = null) {
            #debug($this->request->params);break;

            $requestedFormat = isset($this->request->pass[1]) ? $this->request->pass[1] : false;

            if($mediaID && $requestedFormat) {
                
                // find the filetype
                $theMedia = $this->Media->findById($mediaID);

                // what formats did we receive from the encoder?
                $outputs = json_decode($theMedia['Media']['filename'], true);
                #debug($outputs);
                foreach($outputs['outputs'] as $output) {
                    #debug($output);
                    if($output['label'] == $requestedFormat) $outputTypeFound = true;
                }
                

                if($outputTypeFound) {
                    // yes, we should have this media in the requested format

                    if(!empty($theMedia['Media']['type'])) {
                        // determine what data to send to the browser

                        if($theMedia['Media']['type'] == 'audio') {

                            switch($requestedFormat) {
                                case ('mp3'):
                                    $filetype = array('extension' => 'mp3', 'mimeType' => array('mp3' => 'audio/mp3'));
                                    break;
                                case ('ogg'):
                                    $filetype = array('extension' => 'ogg', 'mimeType' => array('ogg' => 'audio/ogg'));
                                    break;
                            }//switch()

                        } elseif($theMedia['Media']['type'] == 'video') {

                            switch($requestedFormat) {
                                case ('mp4'):
                                    $filetype = array('extension' => 'mp4', 'mimeType' => array('mp4' => 'video/mp4'));
                                    break;
                                case ('webm'):
                                    $filetype = array('extension' => 'webm', 'mimeType' => array('mp4' => 'video/webm'));
                                    break;
                            }//switch()

                        }// audio/video

                        if(isset($filetype)) {
                            // send the file to the browser

                            $this->viewClass = 'Media'; // <-- magic!
                            $params = array(
                                  'id' => $mediaID . '.' . $filetype['extension'], // this is the full filename.. perhaps the one shown to the user if they download
                                  'name' => $mediaID, // this is the filename minus extension
                                  'download' => false, // if true, then a download box pops up
                                  'extension' => $filetype['extension'],
                                  'mimeType' => $filetype['mimeType'],
                                  'path' => ROOT.DS.SITE_DIR.DS.'View'.DS.'Themed'.DS.'Default'.DS.WEBROOT_DIR . DS . 'media' . DS . 'streams' . DS . $theMedia['Media']['type'] . DS
                           );

                           $this->set($params);

                        }
                    }//if(Media.type)

                } else {
                    #$this->Session->setFlash('Requested file format not found.');
                }

            }//if($mediaID && $requestedFormat)

        }//stream()


}//class{}