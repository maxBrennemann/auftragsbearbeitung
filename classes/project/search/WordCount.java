
public class WordCount {

  private String word;
  private int count;
  private double weight;
  private double normalizedWeight;

  /**
   * Creates an instance of this class.
   * 
   * This constructor calls {@link WordCount#WordCount(String, int)} with the
   * parameters set to <code>word</code> and <code>0</code>.
   * 
   * @param word the represented word
   */
  public WordCount(String word) {
    this(word, 0);
  }

  /**
   * Creates an instance of this class representing the specified
   * <code>word</code> with its count set to <code>count</code>.
   * 
   * If the specified word is <code>null</code>, then the word is set to an empty
   * {@link String}. If the specified count is lower than <code>0</code>, then the
   * count is set according to {@link WordCount#setCount(int)}.
   * 
   * @param word  the represented word
   * @param count the count of <code>word</code>
   */
  public WordCount(String word, int count) {
    if (word == null) {
      this.word = "";
    } else {
      this.word = word;
    }

    this.setCount(count);
  }

  /**
   * Returns the represented word.
   * 
   * @return the represented word
   */
  public String getWord() {
    return word;
  }

  /**
   * Returns the count of the represented word.
   * 
   * @return the count of the represented word
   */
  public int getCount() {
    return count;
  }

  /**
   * Sets the count of the represented word.
   * 
   * If the specified count is lower than <code>0</code>, then the count is set to
   * <code>0</code>.
   * 
   * @param count the new count
   */
  public void setCount(int count) {
    if (count < 0) {
      this.count = 0;
    } else {
      this.count = count;
    }
  }

  /**
   * Increases the Count of the represented word by <code>1</code>.
   */
  public int incrementCount() {
    this.count++;
    return this.count;
  }

  /**
   * Increases the count of the represented word by the specified value
   * <code>n</code>.
   * 
   * If the specified value <code>n</code> is lower than <code>0</code>, nothing
   * will happen.
   * 
   * @param n the value by which the count is increased
   */
  public int incrementCount(int n) {
    if (n > 0) {
      this.count += n;
    }
    return this.count;
  }

  /**
   * Returns true, if this instance and the specified {@link WordCount} equal.
   * 
   * @param wordCount the other WordCount
   * @return true, if this instance and the specified {@link WordCount} equal
   */
  public boolean equals(WordCount wordCount) {
    if (wordCount == null)
      return false;
    return this.count == wordCount.count && this.word.equals(wordCount.word);
  }

  /**
   * Returns the weight of this word.
   * 
   * @return the weight of this word
   */
  public double getWeight() {
    return weight;
  }

  /**
   * Sets the weight of this word.
   * 
   * @param weight the new weight
   */
  public void setWeight(double weight) {
    this.weight = weight;
  }

  /**
   * Returns the normalized weight of this word.
   * 
   * @return the normalized weight of this word
   */
  public double getNormalizedWeight() {
    return normalizedWeight;
  }

  /**
   * Sets the normalized weight of this word.
   * 
   * @param normalizedWeight the new normalized weight
   */
  public void setNormalizedWeight(double normalizedWeight) {
    this.normalizedWeight = normalizedWeight;
  }
}
