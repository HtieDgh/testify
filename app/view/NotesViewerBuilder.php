<?php namespace view;
    class NotesViewerBuilder
    {
        public function __construct($title)
        {
            NotesViewerPage::i()->addTitle($title);
        }

        public function addAuthorsBlock($authors, $currentAuthorId=0) : NotesViewerBuilder {
            NotesViewerPage::i()->addAuthorsBlock($authors, $currentAuthorId);
            return $this;
        }
        public function addScrollToTop() : NotesViewerBuilder {
            NotesViewerPage::i()->addScrollToTop();
            return $this;
        }
        public function addControlBtns(): NotesViewerBuilder{
            NotesViewerPage::i()->addControlBtns();
            return $this;
        }
        public function addFooter() : NotesViewerBuilder {
            NotesViewerPage::i()->addFooter();
            return $this;
        }
        public function addHeader() : NotesViewerBuilder {
            NotesViewerPage::i()->addHeader();
            return $this;
        }
        public function addTitle($val) : NotesViewerBuilder{
            NotesViewerPage::i()->addTitle($val);
            return $this;
        }
        public function addBurgerMenu() : NotesViewerBuilder{
            NotesViewerPage::i()->addBurgerMenu();
            return $this;
        }
        public function addNotes(array $notes, string $goToUrl='',string $userInput='',int $authorId=0,int $noteCount=0,int $subId=0,int $pageNum=1,int $courseId=0) : NotesViewerBuilder
        {
            
            Notes::i()->addNotes( $notes );
                        
            Notes::i()->addDropSearch($goToUrl,$userInput,count($notes));
            
            Notes::i()->addPageNavigation($authorId,$subId,$courseId, $pageNum,$noteCount,$goToUrl);
            
            NotesViewerPage::i()->addNotes(Notes::i());
            return $this; 
        }
        /**
         * Возвращает готовое представление просмотра записей
         *
         * @return NotesViewerPage
         */
        public function build() : NotesViewerPage {
            return NotesViewerPage::i();
        }
        

    }
    
?>