<?php
   /*Question_Mapper.php -- Implements the specific code for adding/retrieving a question from DB
   Uses DB.php->execute method for server contact

   questions table layout for future reference
   +------------------------------+
   | id |    question    | reward |
   +-------------------------- ---+
   |  1 | how cool am I? |    4   |
   +----------------------------- +

   */

   require_once APPLICATION_PATH.'model/Answer_Mapper.php';
   require_once APPLICATION_PATH.'model/Answer.php';
   require_once APPLICATION_PATH.'model/Question.php';

   class Question_Mapper extends Mapper{
      private $_answermapper;

      public function __construct() {
         parent::__construct('questions', 'Question');
         $this->_answermapper = new Answer_Mapper();
      }

      private function getRandom($id){
         $query = "
         select *from questions where id not in(
            SELECT questions.id
            from answeredquestions inner join questions on id = questionlink where userlink = ?)
            order by random() limit 1
            ";
            return $this->_db->queryOne($query, $this->_type, array($id));
         }

         public function getRandomQuestion($id){
            //get the corresponding Question from DB
            $question = $this->getRandom($id);
            //get the corresponding answers
            if($question == null){
               return false;
            }
            $answers = $this->_answermapper->getAllWithArgument($question->getId(), 'questionlink');
            $question->setPossibilities($answers);
            return $question;
         }

         public function add($object){
            $question = parent::add($object);
            $object->setId($question->getId());
            foreach($object->getPossibilities() as $value){
               $this->_answermapper->add($value);
            }
         }

         public function get($id, $idname = 'id'){
            $question = parent::get($id, $idname);
            $answers = $this->_answermapper->getAllWithArgument($id, 'questionlink');
            $question->setPossibilities($answers);
            return $question;
         }

         public function updateQuestion($object){
            parent::update($object);
            $fields['questionlink'] = $object->getId();
            $this->_answermapper->delete($fields);
            foreach($object->getPossibilities() as $value){
               $this->_answermapper->add($value);
            }
         }

      }
   ?>