
public class Document {
  
  private DocumentType type;
  private String content;
  
  /**
   * Most common german suffices
   */
  public static final String[] SUFFICES = { "ab", "al", "ant", "artig", "bar", "chen", "ei", "eln", "en", "end", "ent",
  "er", "fach", "fikation", "fizieren", "f‰hig", "gem‰ﬂ", "gerecht", "haft", "haltig", "heit", "ieren", "ig", "in",
  "ion", "iren", "isch", "isieren", "isierung", "ismus", "ist", "it√§t", "iv", "keit", "kunde", "legen", "lein",
  "lich", "ling", "logie", "los", "mal", "meter", "mut", "nis", "or", "sam", "schaft", "tum", "ung", "voll", "wert",
  "w√ºrdig", "ie" };

  /**
   * the words of this document and their counts
   */
  private WordCountsArray wordCounts;

  public Document(String content, DocumentType type) {
    /* use this methods, just in case the value of the parameters is null */
    this.type = type;
    this.content = content;
    this.addContent(content);
  }
  
  public String getContent() {
      return this.content;
  }

  public WordCountsArray getWordCounts() {
    return this.wordCounts;
  }

  /**
   * Splits the specified text into its single words.
   * 
   * This method looks for spaces, splits the specified text at the spaces and
   * returns an array of the single words. We assume that the specified text only
   * consists of lower case letters and case.
   * 
   * @param content the text to split
   * @return an array of single words of the argument
   */
  private static String[] tokenize(String content) {
    int wordCount = 0;

    /* count spaces in the content */
    for (int i = 0; i < content.length(); i++) {
      if (content.charAt(i) == ' ') {
        wordCount++;
      }
    }

    // there is always one word more than there are spaces
    wordCount++;

    // the resulting array
    String[] words = new String[wordCount];

    String word = "";
    int wordIndex = 0;

    for (int i = 0; i <= content.length(); i++) {
      /*
       * reached end of content or end of word important: check end of content first!!
       */
      if (i == content.length() || content.charAt(i) == ' ') {
        if (word.length() > 0) {
          /* put word in array */
          words[wordIndex] = word;
          wordIndex++;

          /* start with empty word for next loop */
          word = "";
        }
      } else {
        /* not end of word: append character */
        word = word + content.charAt(i);
      }
    }

    return words;
  }

  private void addContent(String content) {
    String[] words = Document.tokenize(content);

    this.wordCounts = new WordCountsArray(0);

    for (int i = 0; i < words.length; i++) {
      String word = words[i];

      /* find suffix and cut it */
      String suffix = Document.findSuffix(word);
      word = Document.cutSuffix(word, suffix);

      this.wordCounts.add(word, 1);
    }
  }

  /**
   * Determines, whether the last <code>n</code> characters of the specified
   * <code>String</code>s word1 and word2 are equal.
   * 
   * If <code>n</code> &gt; <code>word1.length()</code> or <code>n</code> &gt;
   * <code>word2.length()</code>, <code>false</code> is returned.
   * 
   * @param word1 the first word
   * @param word2 the second word
   * @param n     how many characters to check
   * @return <code>true</code>, if the last <code>n</code> characters of
   *         <code>word1</code> and <code>word2</code> are equal;
   *         <code>false</code> otherwise
   */
  private static boolean sufficesEqual(String word1, String word2, int n) {
    /* if n is too large, last n chars are not equal */
    if (n > word1.length() || n > word2.length()) {
      return false;
    }

    boolean isEqual = true;
    int i = 0;

    while (isEqual && i < n) {
      /* begin comparison at last char */
      if (word1.charAt(word1.length() - 1 - i) != word2.charAt(word2.length() - 1 - i)) {
        isEqual = false;
      }
      i++;
    }

    return isEqual;
  }

  /**
   * This method utilizes {@link Document#SUFFICES} whether to find out, if the
   * specified <code>word</code> ends with one of these suffices.
   * 
   * @param word
   * @return the suffix of the specified word according to
   *         {@link Document#SUFFICES} or an empty string, if there is no suffix.
   */
  private static String findSuffix(String word) {
    if (word == null || word.equals("")) {
      return null;
    }

    String suffix = "";
    String suffixHit = "";
    int i = 0;

    while (i < Document.SUFFICES.length) {
      suffix = Document.SUFFICES[i];

      /* check, if this suffix is a suffix of word */
      if (sufficesEqual(word, suffix, suffix.length())) {
        if (suffixHit.length() < suffix.length()) {
          suffixHit = suffix;
        }
      }

      i++;
    }
    return suffixHit;
  }

  /**
   * If <code>suffix</code> is a suffix of <code>word</code>, then this suffix is
   * cut off from <code>word</code> and the remaining word stem is returned.
   * 
   * If <code>suffix</code> is not a suffix of <code>word</code>, then the word
   * itself is returned
   * 
   * @param word   the word
   * @param suffix the potential suffix of the word
   * @return the word stem of <code>word</code> with the suffix
   *         <code>suffix</code> cut off; or <code>word</code>, if
   *         <code>suffix</code> is not a suffix of word
   */
  private static String cutSuffix(String word, String suffix) {
    if (suffix == null || suffix.equals("")) {
      return word;
    }

    if (word == null) {
      return null;
    }

    /* not a suffix */
    if (!sufficesEqual(word, suffix, suffix.length())) {
      return word;
    }

    /* create word without suffix, by copying all characters of the word stem */
    String wordWithoutSuffix = "";

    for (int i = 0; i < word.length() - suffix.length(); i++) {
      wordWithoutSuffix = wordWithoutSuffix + word.charAt(i);
    }

    return wordWithoutSuffix;
  }

}