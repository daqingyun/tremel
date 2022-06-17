#include <iostream>
#include <fstream>
#include <sstream>
#include <string>
#include <io.h>
#include "cscan.h"

using namespace std;

char kvalue[10];
struct Record recoreds[2862];
struct Scores scoress[2862];
int i_genes=1, i_terms=1, i_TFs=1;

void filesearch(string path,int layer)
{
	struct _finddata_t filefind;
	string curr=path+"\\*.*";
	
	int done=0, handle;
	//char buf[100];
	ifstream infile;
	ofstream outfile_genes, outfile_terms, outfile_TFs;

	if((handle=_findfirst(curr.c_str(),&filefind))==-1)
		return; 
	while(!(done=_findnext(handle,&filefind)))
	{
		
		if(!strcmp(filefind.name,".."))
		{
			continue;
		}

		if ((_A_SUBDIR==filefind.attrib))
		{
			//
			for (int k = 0; k < strlen(filefind.name); k++)
			{
				if (filefind.name[k] == '_')
				{
					int j = 0;
					for (j = (k+1); j < strlen(filefind.name); j ++)
					{
						kvalue[j-k-1] = filefind.name[j];
					}
					kvalue[j-k-1] = '\0';
					break;
				}
			}
			
			curr=path+"\\"+filefind.name;
			filesearch(curr,layer+1);
		}
		else 
		{	//
			char cluster_no[10];
			for (int kk=0; kk < strlen(filefind.name); kk++)
			{
				if (filefind.name[kk] - '0' <= 9 && filefind.name[kk] - '0' >= 0)
				{
					int jj=0;
					for (jj = kk; jj < strlen(filefind.name); jj++)
					{
						if (filefind.name[jj] == '.')
						{
							break;
						}
						cluster_no[jj-kk] = filefind.name[jj];
					}
					cluster_no[jj-kk] = '\0';
					break;
				}
			}

			
			//char  line[20]={0};
			string line, str1, str2;
			string filename;
			int pos = 0;
			filename = path+"\\"+filefind.name;
			infile.clear();
			infile.open((char*)filename.c_str(),ios::in);
			if(!infile)
			{
				cout << "Open file error." << endl;
			}
			//
			// determine which output file should be written
			switch (filefind.name[0])
			{
			case 'g':
				{
					recoreds[i_genes].index = i_genes;					
					strncpy(recoreds[i_genes].kvalue, kvalue, strlen(kvalue));					
					strncpy(recoreds[i_genes].cluster_no, cluster_no, 10);					

					scoress[i_genes].index = i_genes;
					strncpy(scoress[i_genes].kvalue, kvalue, strlen(kvalue));
					strncpy(scoress[i_genes].cluster_no, cluster_no, 10);

					std::getline(infile, line);
					pos = line.find("\t");
					if (pos < 0)
					{
						printf("find no \\t");
						exit(1);
					}
				
					str1 = line.substr(0,pos);
					str2 = line.substr(pos+1);
					strncat(recoreds[i_genes].names_genes, str1.c_str(), strlen(str1.c_str()));
					strncat(scoress[i_genes].names_genes_score, str2.c_str(), strlen(str2.c_str()));	

					//outfile_genes << line;
					while (std::getline(infile, line))
					{
						pos = line.find("\t");
						if (pos < 0)
						{
							printf("find no \\t");
							exit(1);
						}
						str1 = line.substr(0,pos);
						str2 = line.substr(pos+1);
						strncat(recoreds[i_genes].names_genes, ",", 1);
						strncat(recoreds[i_genes].names_genes, str1.c_str(), strlen(str1.c_str()));
						strncat(scoress[i_genes].names_genes_score, ",", 1);
					    strncat(scoress[i_genes].names_genes_score, str2.c_str(), strlen(str2.c_str()));						
					}
					//cout<<recoreds[i_genes].names_genes;
					
					i_genes++; break;
				}
			case 't':
				{
					recoreds[i_terms].index = i_terms;
					strncpy(recoreds[i_terms].kvalue, kvalue, 10);
					strncpy(recoreds[i_terms].cluster_no, cluster_no, 10);

					scoress[i_terms].index = i_terms;
					strncpy(scoress[i_terms].kvalue, kvalue, 10);
					strncpy(scoress[i_terms].cluster_no, cluster_no, 10);
					
					std::getline(infile, line);
					pos = line.find("\t");
					if (pos < 0)
					{
						printf("find no \\t");
						exit(1);
					}
					str1 = line.substr(0,pos);
					str2 = line.substr(pos+1);
					strncat(recoreds[i_terms].names_terms, str1.c_str(), strlen(str1.c_str()));
					strncat(scoress[i_terms].names_terms_score, str2.c_str(), strlen(str2.c_str()));	

					while (std::getline(infile, line))
					{
						pos = line.find("\t");
						if (pos < 0)
						{
							printf("find no \\t");
							exit(1);
						}
						str1 = line.substr(0,pos);
						str2 = line.substr(pos+1);
						strncat(recoreds[i_terms].names_terms, ",", 1);
						strncat(recoreds[i_terms].names_terms, str1.c_str(), strlen(str1.c_str()));
						strncat(scoress[i_terms].names_terms_score, ",", 1);
						strncat(scoress[i_terms].names_terms_score, str2.c_str(), strlen(str2.c_str()));
					}
					//outfile_terms << endl;

					i_terms++; break;
				}				
			case 'T':
				{
					recoreds[i_TFs].index = i_TFs;
					strncpy(recoreds[i_TFs].kvalue, kvalue, 10);
					strncpy(recoreds[i_TFs].cluster_no, cluster_no, 10);
					
					scoress[i_TFs].index = i_TFs;
					strncpy(scoress[i_TFs].kvalue, kvalue, 10);
					strncpy(scoress[i_TFs].cluster_no, cluster_no, 10);
					
					std::getline(infile, line);
					pos = line.find("\t");
					if (pos < 0)
					{
						printf("find no \\t");
						exit(1);
					}
					str1 = line.substr(0,pos);
					str2 = line.substr(pos+1);
					strncat(recoreds[i_TFs].names_tfs, str1.c_str(), strlen(str1.c_str()));
					strncat(scoress[i_TFs].names_tfs_score, str2.c_str(), strlen(str2.c_str()));

					while (std::getline(infile, line))
					{
						pos = line.find("\t");
						if (pos < 0)
						{
							printf("find no \\t");
							exit(1);
						}
						str1 = line.substr(0,pos);
						str2 = line.substr(pos+1);
						strncat(recoreds[i_TFs].names_tfs, ",", 1);
						strncat(recoreds[i_TFs].names_tfs, str1.c_str(), strlen(str1.c_str()));
						strncat(scoress[i_TFs].names_tfs_score, ",", 1);						
						strncat(scoress[i_TFs].names_tfs_score, str2.c_str(), strlen(str2.c_str()));
					}
					//outfile_TFs << endl;
					i_TFs++; break;
				}
			}
			infile.close();
			//i++;
		}
	}
	
	_findclose(handle);
// 	cout<<i_genes<<endl<<endl;
// 	cout<<i_terms<<endl<<endl;
// 	cout<<i_TFs<<endl<<endl;
}

int main()
{
	ofstream out,out2;
	out.open("C:\\Users\\Andrew\\Desktop\\data\\GENSEQ.TXT",ios::app);
	out2.open("C:\\Users\\Andrew\\Desktop\\data\\SCORE.TXT",ios::app);
	string path = "C:\\Users\\Andrew\\Desktop\\stemmed";
	//string path = "C:\\clusters";
// 	cout<<"input:"<<endl;
// 	cin>>path;
	filesearch(path,10);
	int counter = 1;
	//cout<<recoreds[1].kvalue;

	for (int i = 1; i <= 2861; i++)// kvalue
	{
		for (int j =1; j <= 2861; j++)// cluster_no
		{
			for (int k=1; k <= 2861; k++)
			{
				//cout<<recoreds[k].kvalue;
				if((atoi(recoreds[k].kvalue) == i) &&
					(atoi(recoreds[k].cluster_no)==j))
				{
					out << counter << "\t";
					out << i << "\t";
					out << j << "\t";
					out << recoreds[k].names_genes << "\t";
					out << recoreds[k].names_terms << "\t";
					out << recoreds[k].names_tfs << endl;

					out2 << counter << "\t";
					out2 << i << "\t";
					out2 << j << "\t";
					out2 << scoress[k].names_genes_score << "\t";
					out2 << scoress[k].names_terms_score << "\t";
					out2 << scoress[k].names_tfs_score << endl;
					counter++;
				}
			}
		}
	}
	out.close();
	out2.close();

	system("PAUSE");
	return 0;
}
