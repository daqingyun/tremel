#ifndef _C_SCAN_H_
#define _C_SCAN_H_

#include <iostream>
#include <fstream>
#include <sstream>
#include <string>

struct Record{
	int index;
	char kvalue[10];
	char cluster_no[10];
	char names_genes[2048]; // includes terms, genes, tfs
	char names_terms[2048];
	char names_tfs[2048];
};

struct Scores{
	int index;
	char kvalue[10];
	char cluster_no[10];
	char names_genes_score[2048]; // includes terms, genes, tfs
	char names_terms_score[2048];
	char names_tfs_score[2048];
};


#endif