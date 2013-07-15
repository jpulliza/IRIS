#Functionality
Here is a list of all of the current controllers:
- [summarization](#summarization) - summarize with Lemur
- [cluster](#clustering) - cluster with Lemur
- [merge](#merge) - merge multiple resourceList elements into one
- [pipe](#pipe) - do a Unix like pipe by using the output of a request as input to the next
- [limit](#limit) - SQL style limit of documents returned
- [sort](#sort) - sort documents on a supplied field
- [extract](#extract) - extract keywords by frequency
- [filter](#filter) - remove words from document content
- [query](#query) - do a boolean type query on a group of documents
- [rank](#rank) - rank documents based on frequency of words supplied
- [vector_rank](#vector-rank) - rank documents on a vector model
- [index_insert](#index-insert) - create or add to index
- [index_query](#index-query) - do an Indri-like query on an index
- [index_delete](#index-delete) - delete an index
- [fetch](#fetch) - fetch document content
- [extract_blocks](#extract-blocks) - search for a block of text
- [summarize_sentences](#summarize-sentences) - gives the most important sentences as a summarization
- [if_then](#if-then) - perform an if-then form of control flow
- [halt](#halt) - stop execution of a pipe

#Directory Structure
- bin/ - Executable files (compiled on Linux Mint)
- extension/ - written in C to handle text processing, compiled version is called text_processing and is in bin folder
- controllers/ - files which perform specific tasks (e.g. clustering, summarization, etc.)
- library/ - helpful methods shared among all controllers
- storage/ - folder where all of the request/response data is stored, where all of the necessary indexing files are created (and removed)
- tests/ - this folder has all of our testing XML files, most of which are very simple and used for debugging, however, under tests/higher_behavior, there are more interesting XML tests using piping to do more complex actions
- config.php - configuration for debugging and file paths
- dbconfig.php.example - configuration file for database (remove the trailing .example to use)

#Adding a new controller to the API
To add a new controller, add the file to the controllers directory, give it the same name as the request type. E.G. if you wanted to add a controller for 'keywords' add a file keywords.php to controllers. Then, in config.php, add 'keywords' to the VALID_REQUEST_TYPES array. An example input that would access that controller would look like:
```
<parameters>
<requestType>keywords</requestType>
... (other parameters)
</parameters>
```
#Request and Response Format:

##<a id="Summarization"></a>Summarization
###Request
```
<parameters>
	<requestType>summarize</requestType>
	<maxSentences>10</maxSentences>
	<individualSummaries>TRUE</individualSummaries>
	<docList>
		<doc>
			<docID>1</docID>
		</doc>
		<doc>
			<docID>2</docID>
		</doc>
		<doc>
			<docID>3</docID>
		</doc>
	</docList>
</parameters>

```
Here,
- &lt;maxSentences&gt;: up to how many words to produce in the summary
- &lt;individualSummaries&gt;: create individual summaries for each document? Here it's set to "TRUE", which means you'd output three summaries (there are three documents).

###Response
```
<parameters>
	<requestID>123</requestID>
	<requestType>summarize</requestType>
	<docList>
	<doc>
	<docID>1</docID>
		<summary>
		stuff
		</summary>
	</doc>
	<doc>
	<docID>2</docID>
		<summary>
		stuff
		</summary>
	</doc>
	<doc>
	<docID>3</docID>
		<summary>
		stuff
		</summary>
	</doc>
	</docList>
</parameters>

```
##<a id="Clustering"></a>Clustering
###Request
```
<parameters>
	<requestType>cluster</requestType>
	<numClusters>3</numClusters>
	<docList>
		<doc>
			<docID>1</docID>
		</doc>
		<doc>
			<docID>2</docID>
		</doc>
		<doc>
			<docID>3</docID>
		</doc>
		<doc>
			<docID>4</docID>
		</doc>
		<doc>
			<docID>5</docID>
		</doc>
		<doc>
			<docID>6</docID>
		</doc>
	</docList>
</parameters>

```

###Response
```
<parameters>
	<requestID>123</requestID>
	<requestType>cluster</requestType>
	<clusterList>
	<cluster>
		<clusterID>1</clusterID>
		<docList>
			<doc>
				<docID>1</docID>
				<title>abc</title>
			</doc>
				<docID>3</docID>
				<title>xyz</title>
			</doc>
		</docList>
	</cluster>
	<cluster>
		<clusterID>2</clusterID>
		<docList>
			<doc>
				<docID>2</docID>
				<title>abc</title>
			</doc>
				<docID>6</docID>
				<title>xyz</title>
			</doc>
		</docList>
	</cluster>
	<cluster>
		<clusterID>3</clusterID>
		<docList>
			<doc>
				<docID>4</docID>
				<title>abc</title>
			</doc>
				<docID>5</docID>
				<title>xyz</title>
			</doc>
		</docList>
	</cluster>
</parameters>
```

##<a id="Error"></a>Error
###Response
```
<parameters>
	<error>message</error>
</parameters>
```

#Low Level Functionality (in progress)
##Some Extra Notes:
As of now, the client id is passed with every request in a clientID element. This will change with the addition of user authentication.

There is also the option of specifying a user id to add an extra layer of page specificity. That way pages are stored per user id, so two different users could have two different pages with the same id both stored on the server

###Format of resource:
- id - unique number as specified by client, this must be unique for each page, we will use this to store in our indices and we will return the page id's
- url - optional, if the page is a webpage, we can fetch the content from a URL
- content - optional, you can specify the content (HTML or plain text) directly in the XML request

###Notes about resources:
Resources are a generalization of an entity of information. A resource can be a webpage or user specified content. A client wanting to send resources to our API will send them in a <b>resourceList</b> element with the required information described below.

For a resource, you must specify either the <b>url</b> (if it is a web page) or the <b>content</b> element with the plain text data.

Furthermore, resources are stored for caching if the <b>persistence</b> element is specified. If persistence is specified, then the pages will be stored on our server for caching, which can reduce response time and allow for more complicated requests (involving Indri indices). However, this means that each resource must be uniquely identified. 

Identification of a resource happens on two or three levels. The client using the API will have a client id. Each resource the client sends must include a unique <b>id</b> element. Also there is an optional user id which can be used to differentiate between users on the client's system.

On the initial call to the API, the client will have to specify either the content or URL for all of the pages passed. However, a benefit of enabling persistence on pages is that for any later calls on the same pages, the client will only need to specify the id element.

Controllers which modify content (e.g. filter) will return the content element with a type attribute indicating that it has been modified. For example, calling the filter controller will return content with type="filtered". Even having persistence enabled will not store modified content.

Example request:
```
<persistence>TRUE</persistence>
...
<resourceList>
	<resource>
		<id>page ID</id>
		<url>http://...</url>
	</resource>
	<resource>
		<id>page ID</id>
		<content>Some lengthy text here...</content>
	</resource>
</resourceList>

```

##<a id="Select"></a>Select
####(deprecated)
###Request
The field operator allows you to select from predefined fields based on the table (e.g. you can add a field of "url" or "snippetID" if the table value is "snippet").
Not including the fields list will return no fields, but can still be useful for only retrieving the id's of the resulting resources.

The &lt;logic&gt; tags wrap fields in the &lt;where&gt; clause for logical connectives.
```
<parameters>
	<requestType>select</requestType>
	<fields>
		<field>
			field name
		</field>
		<field>
			field name
		</field>
		...
	</fields>
	<table>
		table name (pages|annotation|snippet|bookmarks|searches)
	</table>
	<where>
		(<logic type="and|or|not")
			<field operator="eq|ne|lt|gt|lte|gte|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			<field operator="eq|ne|lt|gt|lte|gte|like|in">
				<name>
					field name
				</name>
				<value>
					test value
				</value>
			</field>
			...
		(</logic>)
		...
	</where>
	(<orderby type="desc|asc">
		<field>
			field name
		</field>
	 </orderby>)
	 (<limit>
	 	number
	 (</limit)
</parameters>
```

###Response
```
<parameters>
	<requestID>number</requestID>
	<table>table name</table>
	<requestType>select</requestType>
	<resourceList>
		...
	</resourceList>
</parameters>
```

##<a id="Merge"></a>Merge
Merge requests can easily merge multiple resource lists
###Request
```
<parameters>
	<requestType>merge</requestType>
	<table>table name</table>
	<resourceList>
		...
	</resourceList>
	<resourceList>
		...
	</resourceList>
	...
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>merge</requestType>
	<table>table name</table>
	<resourceList>
		...
	</resourceList>	
</parameters>
```
##<a id="Insert"></a>Insert
####(deprecated)
###Request
```
<parameters>
	<requestType>insert</requestType>
	<table>table name</table>
	<fields>
		<field>
			<name>
				field name
			</name>
			<value>
				field value
			</value>
		</field>
		...
	</fields>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<table>table name</table>
	<insertID>number</insertID>
	<requestType>insert</requestType>
</parameters>
```
##<a id="Update"></a>Update
####(deprecated)
###Request
```
<parameters>
	<requestType>update</requestType>
	<table>table name</table>
	<fields>
		<field>
			<name>
				field name
			</name>
			<value>
				field value
			</value>
		</field>
		...
	</fields>
	<resourceList>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<table>table name</table>
	<requestType>update</requestType>
	<resourceList>
		...
	</resourceList>
</parameters>
```
##<a id="Delete"></a>Delete
####(deprecated)
###Request
```
<parameters>
	<requestType>delete</requestType>
	<table>table name</table>
	<resourceList>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
<table>table name</table>
	<requestID>number</requestID>
	<requestType>delete</requestType>
</parameters>
```

##<a id="Pipe"></a>Pipe
The pipe command allows you to do a unix-like pipe feeding the output of one command into the input of another. You cannot do this by simply taking the XML output of one and passing it to another command since it needs a bit of reformatting. This allows multiple commands to be easily strung together and called repeatedly without much work of the client.

Subtle Rules:

If you pipe to rank/filter/query, and do not supply a wordlist element, it will automatically use the content of the first resource of the first resourceList in queue
###Request
```
<parameters>
	<requestType>pipe</requestType>
	<command>
		(Any of the input formats for the commands)
	</command>
	<command>
		(This command will get the resourceList input from the previous command, therefore it is unnecessary to include a resourceList in this command.)
	</command>
	...
</parameters>
```
###Response
The response will follow the format of the last executed command.

##<a id="Limit"></a>Limit
The limit request allows you to select a subset of results. The offset is optional and defaults to 0.
###Request
```
<parameters>
	<requestType>limit</requestType>
	<table>table name</table>
	(<offset>number</offset>)
	<amount>number</amount>
	<resourceList>
		<resource>
			<table>table name</table>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<table>table name</table>
	<requestType>limit</requestType>
	<resourceList>
		<resource>
			<table>table name</table>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="Sort"></a>Sort
Sort a resource list
###Request
```
<parameters>
	<requestType>sort</requestType>
	<table>table name</table>
	<orderby type="desc|asc"> 
		field name (defaults to id)
	</orderby>
	<resourceList>
		<resource>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<table>table name</table>
	<requestType>sort</requestType>
	<resourceList>
		<resource>
			<id>id</id>
		</resource>
		...
	</resourceList>
</parameters>
```
##<a id="Extract"></a>Extract
###Request
```
<parameters>
	<requestType>extract</requestType>
	<numWords>number</numWords>
	<resourceList>
		<resource>
			<id>id</id>
			<content>
				data
			</content>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>extract</requestType>
	<resourceList>
		<resource>
			<id>id</id>
			<keywords>comma,seperated,keywords</keywords>
			<content type="extracted">
				data
			</content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="Filter"></a>Filter
###Request
The wordList parameter (optional) are the words you wish to remove from the content

```
<parameters>
	<requestType>filter</requestType>
	<wordList>words</wordList>
	<minLength>number</minLength>
	<maxLength>number</maxLength>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>filter</requestType>
	<resourceList>
		<resource>
			<id>id</id>
			<content type="filtered"></content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="Query"></a>Query
Performs simple queries on documents
###Request
```
<parameters>
	<requestType>query</requestType>
	<wordList>list of words to check</wordList>
	<query>
		<type>eq|ne|lt|gt</type>
		<value>value</value>
		<useStemming>TRUE|FALSE</useStemming>
	</query>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```

###Response
```
<parameters>
	<requestType>query</requestType>
	<requestID>number</requestID>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="Rank"></a>Rank
Ranks documents based on a supplied list of words. The ranking is based on total number of occurences of the words supplied.
###Request
```
<parameters>
	<requestType>rank</requestType>
	<wordList>list of words to check</wordList>
	<resourceList>
		<resource>
			<id>id</id>
			<rank>number</rank>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="vector_rank"></a>Vector Rank
Ranks documents using a vector model of the wordList you supply and returns a rank based on the cosine similarity of the query and the document
###Request
```
<parameters>
	<requestType>vector_rank</requestType>
	<wordList>list of words to check</wordList>
	<resourceList>
		<resource>
			<id>id</id>
			<rank>number</rank>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="index_insert"></a>Index Insert
###Request
```
<parameters>
	<requestType>index_insert</requestType>
	<indexID>optional</indexID>
	<persistence>TRUE|FALSE</persistence>
	<resourceList>
		<resource>
			<url></url>
		</resource>
		<resource>
			<url></url>
		</resource>
		<resource>
			<url></url>
		</resource>
		<resource>
			<url></url>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<indexID>number</indexID>
	<requestType>index_insert</requestType>
</parameters>
```
##<a id="index_delete"></a>Index Delete
###Request
```
<parameters>
	<requestType>index_delete</requestType>
	<indexID>required</indexID>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<indexID>number</indexID>
	<requestType>index_delete</requestType>
</parameters>
```

##<a id="index_query"></a>Index Query
###Request
The query element is mostly the same as the query element describe in the Indri documentation [here](http://sourceforge.net/p/lemur/wiki/IndriRunQuery/) however, you can only specify one query at the moment.
```
<parameters>
	<requestType>index_query</requestType>
	<indexID>number</indexID>
	<query>
		indri type query
	</query>
</parameters>
```
###Response
The Indri resulting score is the logarithm of the probability, therefore the more negative the score is, the lower the rank, and vice-versa
```
<parameters>
	<requestType>index_query</requestType>
	<requestID>number</requestID>
	<indexID>number</indexID>
	<resourceList>
		<resource>
		 	<score>indri query score (sorted in descending order)</score>
		 	<url>page url</url>
		 </resource>
		...
	</resourceList>
</parameters>
```


##<a id="fetch"></a>Fetch
Fetch gets the content of the passed url's
###Request
```
<parameters>
	<requestType>fetch</requestType>
	<resourceList>
		<resource>
			<url>page url</url>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestType>fetch</requestType>
	<requestID>number</requestID>
	<resourceList>
		<resource>
			<url>page url</url>
			<content>page content</content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="extract_blocks"></a>Extract Blocks
###Request
```
<parameters>
	<requestType>extract_blocks</requestType>
	<wordList></wordList>
	<searchWindow>num of words</searchWindow>
	<resultWindow>num of words</resultWindow>
	<useStemming>TRUE|FALSE</useStemming>
	<resourceList>
		<resource>
			<url>page url</url>
			<content>page content</content>
		</resource>
		...
	</resourceList>
</parameters>
```

###Response
```
<parameters>
	<requestType>extract_blocks</requestType>
	<requestID>number</requestID>
	<resourceList>
		<resource>
			<url>page url</url>
			<blockList>
				<block>
					block text
				</block>
				...
			</blockList>
			<content>page content</content>
		</resource>
		...
	</resourceList>
</parameters>
```

##<a id="summarize_sentences"></a>Summarize Sentences
This simple summarization controller will use the words in the wordList passed and deem the most important sentences of a document by which those words appear the most.

At the moment, the word list can have repeated words (which will weight those words higher). This will most likely be removed for consistency.
```
<parameters>
	<requestType>summarize_sentences</requestType>
	<numSentences>number</numSentences>
	<wordList></wordList>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
```
<parameters>
	<requestID>number</requestID>
	<requestType>summarize_sentences</requestType>
	<resourceList>
		<resource>
			<id>id</id>
			<content type='summarized'></content>
		</resource>
		...
	</resourceList>
</parameters>
```


##If Then
Perform a basic if-then control statement. 

The val element can be in [xpath](http://www.php.net/manual/en/simplexmlelement.xpath.php) format to access XML nodes or a literal number/string.

You can test the number of nodes returned by setting fxn to "length". You can also test for the existence of a node by using the "exists" test. Exists only requires 1 val element. Otherwise all of the other operators require 2 val elements. The nth attribute can be used to get a specific node.

The motivation behind this is for the if-then control to be used in conjunction with pipe requests to reduce total number of requests. This can be seen under examples.

###Request
```
<parameters>
	<requestType>if_then</requestType>
	<if>
		<statement>
			<val type="xpath|literal" nth="" fxn="length">value (optional)</val>
			<test>eq|ne|lt|lte|gt|gte|exists (optional)</test>
			<val type="xpath|literal" nth="" fxn="length">value (optional)</val>
		</statement>
		<command>
			...
		</command>
	</if>
	<elif>
		(optional)
		<statement>
			...
		</statement>
		<command>
			...
		</command>
	</elif>
	<else>
		(optional)
		<command>
			...
		</command>
	</else>
	<resourceList>
		<resource>
			<id>id</id>
			<content></content>
		</resource>
		...
	</resourceList>
</parameters>
```
###Response
The response will be the response of the command executed from the structure OR if no command is executed (e.g. if statement is false and no else is specified) it will return the following:
```
<parameters>
	<requestType>if_then</requestType>
	<requestID></requestID>
	<status>No branch taken</status>
</parameters>
```

##Halt
The halt operator stops execution and is supposed to be used in conjunction with pipe requests and if_then requests to stop piping if certain conditions are met.
###Request
```
<parameters>
	<requestType>halt</requestType>
</parameters>
```
###Response
```
<parameters>
	<requestType>halt</requestType>
	<requestID></requestID>
</parameters>
```