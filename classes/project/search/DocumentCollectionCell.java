/**
 * Helper class for the cells of the {@link DocumentCollection}.
 * 
 * @see DocumentCollection
 * 
 */
public class DocumentCollectionCell {
  /**
   * the document in this cell
   */
  private Document document;

  /**
   * pointer to the next cell
   */
  private DocumentCollectionCell next;

  /**
   * the similarity of the document in this cell
   */
  private double querySimilarity;

  /**
   * Constructs a new instance.
   * 
   * @param document the {@link Document} in the cell
   * @param next     pointer to the next cell
   */
  public DocumentCollectionCell(Document document, DocumentCollectionCell next) {
    this.document = document;
    this.next = next;
    this.querySimilarity = 0;
  }

  /**
   * Returns the next {@link DocumentCollectionCell}
   * 
   * @return the next {@link DocumentCollectionCell}
   */
  public DocumentCollectionCell getNext() {
    return next;
  }

  /**
   * Set the next {@link DocumentCollectionCell} to the specified value
   * 
   * @param next the next {@link DocumentCollectionCell}
   */
  public void setNext(DocumentCollectionCell next) {
    this.next = next;
  }

  /**
   * Returns the similarity of the {@link Document} in this cell
   * 
   * @return the similarity of the {@link Document} in this cell
   */
  public double getQuerySimilarity() {
    return querySimilarity;
  }

  /**
   * Sets the similarity of the {@link Document} in this cell
   * 
   * @param querySimilarity the similarity of the {@link Document}
   */
  public void setQuerySimilarity(double querySimilarity) {
    this.querySimilarity = querySimilarity;
  }

  /**
   * Returns the {@link Document} in this cell.
   * 
   * @return the {@link Document} in this cell.
   */
  public Document getDocument() {
    return document;
  }

  /**
   * Sets the {@link Document} in this cell and returns the {@link Document} that
   * used to be in this cell.
   * 
   * @param document the new {@link Document} in this cell
   * @return the {@link Document} that used to be in this cell
   */
  public Document setDocument(Document document) {
    Document oldDocument = this.document;
    this.document = document;
    return oldDocument;
  }
}