
public class DocumentCollection {
  
  private DocumentCollectionCell first;
  private DocumentCollectionCell last;
  private int size;
  private DocumentType type;

  public DocumentCollection() {
    this.first = null;
    this.last = null;
    this.size = 0;
  }

  /**
   * Inserts the specified {@link Document} at the beginning of the collection.
   * 
   * Nothing will happen, if the specified {@link Document} is <code>null</code>.
   * 
   * @param doc the {@link Document} to add
   */
  public void prependDocument(Document doc) {
    if (doc == null) {
      return;
    }

    if (this.isEmpty()) {
      /* list empty, add as one and only element */
      this.first = new DocumentCollectionCell(doc, null);
      this.last = first;
    } else {
      this.first = new DocumentCollectionCell(doc, first);
    }
    
    size++;
  }

  /**
   * Inserts the specified {@link Document} at the end of the collection.
   * 
   * Nothing will happen, if the specified {@link Document} is <code>null</code>.
   * 
   * @param doc the {@link Document} to add
   */
  public void appendDocument(Document doc) {
    if (doc == null) {
      return;
    }

    if (this.isEmpty()) {
      /* list empty, add as only element */
      this.first = new DocumentCollectionCell(doc, null);
      this.last = first;
    } else {
      last.setNext(new DocumentCollectionCell(doc, null));
      last = last.getNext();
    }

    size++;
  }

  /**
   * Returns the index in this collection of the specified {@link Document}.
   * 
   * If the specified {@link Document} is contained in the collection more than
   * once, then the lowest index is returned. If the {@link Document} is not
   * contained in the list or if <code>doc</code> is <code>null</code>, then
   * <code>-1</code> will be returned.
   * 
   * @param doc the {@link Document} to look for
   * @return the index in this collection of the specified document
   */
  public int indexOf(Document doc) {
    if (doc == null || this.isEmpty()) {
      return -1;
    }

    /* loop over list and find document */

    DocumentCollectionCell tmp = this.first;
    int index = 0;

    while (tmp != null) {
      if (tmp.getDocument().equals(doc)) {
        return index;
      }

      tmp = tmp.getNext();
      index++;
    }

    return -1;
  }

  /**
   * Returns <code>true</code>, if the specified {@link Document} is contained in
   * this collection.
   * 
   * @param doc the {@link Document}
   * @return <code>true</code>, if the specified {@link Document} is contained in
   *         this collection
   */
  public boolean contains(Document doc) {
    return (this.indexOf(doc) != -1);
  }

  /**
   * Removes the element at the specified index.
   * 
   * If the specified index is invalid or if this collection is empty, nothing
   * will happen.
   * 
   * @param index the index of the element to be deleted
   */
  public boolean remove(int index) {
    if (index < 0 || index >= this.numDocuments()) {
      return false;
    }

    if (this.isEmpty()) {
      return false;
    }

    /* remove first */
    if (index == 0) {
      this.removeFirstDocument();
      return true;
    }

    /* remove last */
    if (index == this.numDocuments() - 1) {
      this.removeLastDocument();
      return true;
    }

    /* we will only get here, if index >= 1 and size >= 2 */

    /* loop to index, keep track of previous */
    DocumentCollectionCell actual = this.first.getNext();
    DocumentCollectionCell prev = this.first;
    int i = 1;

    while (i < index) {
      prev = actual;
      actual = actual.getNext();
      i++;
    }

    /* delete actual */
    prev.setNext(actual.getNext());
    size--;
    return true;
  }

  /**
   * Removes the last element from the collection.
   * 
   * If the collection is empty, nothing will happen. If the collection has size
   * <code>1</code>, the collection will be empty afterwards.
   */
  public void removeLastDocument() {
    if (this.isEmpty()) {
      return;
    }

    /* one element: clear list and return */
    if (this.numDocuments() == 1) {
      this.clear();
      return;
    }

    /* navigate to the element before last */
    DocumentCollectionCell newLast = this.first;
    while (newLast.getNext() != this.last) {
      newLast = newLast.getNext();
    }

    /* assign new last element */
    newLast.setNext(null);
    this.last = newLast;
    size--;
  }

  /**
   * Removes the first element from the collection.
   * 
   * If the collection is empty, nothing will happen. If the collection has size
   * <code>1</code>, the collection will be empty afterwards.
   */
  public void removeFirstDocument() {
    if (this.isEmpty()) {
      return;
    }

    /* one element: clear list and return */
    if (this.numDocuments() == 1) {
      this.clear();
      return;
    }

    /* remove first element */
    this.first = this.first.getNext();
    size--;
  }

  /**
   * Returns the first element of the collection or <code>null</code>, if it is
   * empty.
   * 
   * @return the first element of the collection or <code>null</code>, if it is
   *         empty
   */
  public Document getFirstDocument() {
    if (this.isEmpty()) {
      return null;
    }

    return this.first.getDocument();
  }

  /**
   * Returns the last element of the collection or <code>null</code>, if it is
   * empty.
   * 
   * @return the last element of the collection or <code>null</code>, if it is
   *         empty
   */
  public Document getLastDocument() {
    if (this.isEmpty()) {
      return null;
    }

    return this.last.getDocument();
  }

  /**
   * Deletes all elements from the collection.
   */
  private void clear() {
    this.first = null;
    this.last = null;
    this.size = 0;
  }

  /**
   * Determines, whether this collection is empty.
   * 
   * @return <code>true</code>, if this collection is empty, <code>false</code>
   *         otherwise
   */
  public boolean isEmpty() {
    return this.size == 0;
  }

  /**
   * Returns the number of {@link Document}s in this collection.
   * 
   * @return the number of {@link Document}s in this collection
   */
  public int numDocuments() {
    return this.size;
  }

  /**
   * Returns the {@link Document} in this collection at the specified index.
   * 
   * If the specified index is invalid, this method will return <code>null</code>.
   * 
   * @param index the index of which we want to get the {@link Document}
   * @return the {@link Document} at index <code>index</code> or
   *         <code>null</code>, if the specified index is invalid
   */
  public Document get(int index) {
    if (index < 0 || index >= this.size) {
      return null;
    }

    return getDocumentCollectionCell(index).getDocument();
  }

  /**
   * This method returns a {@link WordCountsArray} containing all words of all
   * {@link Document}s in this collection.
   * 
   * Note, that no word will be contained twice in the resulting
   * {@link WordCountsArray}. The count of every word in the resulting
   * {@link WordCountsArray} will be <code>0</code>.
   * 
   * @return a {@link WordCountsArray} containing all words of all
   *         {@link Document}s in this collection
   */
  private WordCountsArray allWords() {
    /*
     * loop over all documents to create a WordCountsArray containing *all* words of
     * all documents
     */
    DocumentCollectionCell tmp = this.first;

    WordCountsArray allWords = new WordCountsArray(0);

    while (tmp != null) {
      Document doc = tmp.getDocument();
      WordCountsArray wca = doc.getWordCounts();

      for (int i = 0; i < wca.size(); i++) {
        allWords.add(wca.getWord(i), 0);
      }

      tmp = tmp.getNext();
    }

    return allWords;
  }

  /**
   * This method calculates the similarity between the specified query and all
   * {@link Document}s in this {@link DocumentCollection} and sorts the
   * {@link Document}s in this collection according to the calculated similarity.
   * 
   * @param searchQuery the query String
   */
  public void match(String searchQuery) {
    if (this.isEmpty()) {
      return;
    }

    if (searchQuery == null || searchQuery.equals("")) {
      return;
    }

    /* add query to collection as document */
    Document queryDocument = new Document(searchQuery, type);
    this.prependDocument(queryDocument);

    /* add every word to every document with count 0 */
    this.addZeroWordsToDocuments();

    /* sort all WordCountsArrays of all documents */
    DocumentCollectionCell tmp = this.first;
    while (tmp != null) {
      tmp.getDocument().getWordCounts().sort();
      tmp = tmp.getNext();
    }

    /* calculate similarities with query document */
    tmp = this.first.getNext();
    while (tmp != null) {
      WordCountsArray wca1 = tmp.getDocument().getWordCounts();
      WordCountsArray wca2 = queryDocument.getWordCounts();
      //tmp.setQuerySimilarity(wca1.computeSimilarity(wca2));
      tmp.setQuerySimilarity(wca1.computeSimilarity(wca2, this));
      tmp = tmp.getNext();
    }

    /* remove the query we added in the beginning */
    this.removeFirstDocument();

    this.sortBySimilarityDesc();
  }

  /**
   * This private helper method swaps the content of the two specified
   * {@link DocumentCollectionCell}s of this {@link DocumentCollection}.
   * 
   * @param cell1 the first {@link DocumentCollectionCell}
   * @param cell2 the second {@link DocumentCollectionCell}
   */
  private void swap(DocumentCollectionCell cell1, DocumentCollectionCell cell2) {
    /*
     * swap both contained the contained document and the corresponding similarity
     */

    Document tmpDoc = cell1.getDocument();
    double tmpSim = cell1.getQuerySimilarity();

    cell1.setDocument(cell2.getDocument());
    cell1.setQuerySimilarity(cell2.getQuerySimilarity());

    cell2.setDocument(tmpDoc);
    cell2.setQuerySimilarity(tmpSim);
  }

  /**
   * This method sorts the documents in this collection descending, according to
   * their similarity. The similarity of each document is calculated by calling
   * {@link DocumentCollection#match(String)} and then stored in the corresponding
   * {@link DocumentCollectionCell}.
   */
  private void sortBySimilarityDesc() {
    for (int pass = 1; pass < this.numDocuments(); pass++) {

      DocumentCollectionCell actCell = this.first;

      for (int i = 0; i < this.numDocuments() - pass; i++) {
        /* swap content of cells, if cells are in wrong order */
        if (actCell.getQuerySimilarity() < actCell.getNext().getQuerySimilarity()) {
          swap(actCell, actCell.getNext());
        }

        actCell = actCell.getNext();
      }
    }
  }

  /**
   * This method gets a set of all words of all {@link Document}s in this
   * collection and adds every word to every {@link Document} in this collection
   * with count <code>0</code>.
   * 
   * After this method has been executed, all {@link Document}s in this administer
   * the same words. Note, that the words are always added with count
   * <code>0</code>.
   * 
   */
  private void addZeroWordsToDocuments() {
    WordCountsArray allWords = this.allWords();

    DocumentCollectionCell tmp = this.first;

    while (tmp != null) {
      for (int j = 0; j < allWords.size(); j++) {
        String word = allWords.getWord(j);

        tmp.getDocument().getWordCounts().add(word, 0);
      }

      tmp = tmp.getNext();
    }
  }

  /**
   * This method returns the similarity of the {@link Document} at the specified
   * index. This similarity must have been calculated before using the method
   * {@link DocumentCollection#match(String)}.
   * 
   * If the specified index is invalid, <code>-1</code> is returned.
   * 
   * @param index the index
   * @return the similarity of the {@link Document} at the specified index
   */
  public double getQuerySimilarity(int index) {
    if (index < 0 || index >= this.numDocuments()) {
      return -1;
    }

    return this.getDocumentCollectionCell(index).getQuerySimilarity();
  }

  /**
   * Returns the {@link DocumentCollectionCell} that is at the specified index in
   * this {@link DocumentCollection}.
   * 
   * @param index the index
   * @return the {@link DocumentCollectionCell} at the specified index
   */
  private DocumentCollectionCell getDocumentCollectionCell(int index) {
    if (index < 0 || index >= this.numDocuments()) {
      return null;
    }

    /* navigate to corresponding cell at the index */

    DocumentCollectionCell tmp = this.first;

    int i = 0;
    while (i < index) {
      tmp = tmp.getNext();
      i++;
    }

    return tmp;
  }
  
  /**
   * Returns the number of documents that contain the specified word.
   * 
   * If the word is contained in the {@link WordCountArray} of a {@link Document}
   * with count <code>0</code>, then it is considered as not being contained in
   * the document.
   * 
   * @param word the word of which the number of containing documents is returned.
   * @return the number of documents that contain the specified word
   */
  public int noOfDocumentsContainingWord(String word) {
    if (word == null) {
      return 0;
    }

    int count = 0;

    /* loop over all documents and check if word is contained */
    for (int i = 0; i < this.numDocuments(); i++) {
      WordCountsArray wca = this.get(i).getWordCounts();

      if (wca != null) {
        int index = wca.getIndexOfWord(word);

        /*
         * increase count only if count of this word in the WordCountArray is greater
         * than 0
         */
        if (index != -1 && wca.getCount(index) > 0) {
          count++;
        }
      }
    }

    return count;
  }
}
